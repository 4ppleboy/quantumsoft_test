<?php

namespace app;

class DatabaseState
{
    private $state;
    private $created;
    private $deleted;
    private $rejected;

    public function __construct(array $state)
    {
        $this->state = $state;
        $this->created = [];
        $this->deleted = [];
        $this->rejected = [];
    }

    public function get(): array
    {
        return $this->state;
    }

    public function findPathTo($id, array $haystack = null, array &$path = [])
    {
        if (null === $haystack) {
            $haystack =& $this->state;
        }

        foreach ($haystack as $key => $value) {
            if (\is_array($value)) {
                $path [] = $key;
                if ($this->findPathTo($id, $value, $path)) {
                    return $path;
                }

                array_pop($path);
            } elseif ($key === 'id' && $value === $id) {
                return true;
            }
        }

        return false;
    }

    public function getNode(array $sequence): ?array
    {
        $sequence_length = \count($sequence);
        $aux =& $this->state;
        foreach ($sequence as $iterator => $index) {
            if (isset($aux[$index])) {
                $aux =& $aux[$index];
            } else {
                return null;
            }
            if ($sequence_length === $iterator + 1 && isset($aux['id'], $aux['name'])) {
                return $aux;
            }
        }

        return null;
    }

    public function goto(array $sequence, \Closure $closure)
    {
        $aux =& $this->state;
        foreach ($sequence as $index) {
            if (isset($aux[$index])) {
                $aux =& $aux[$index];
            } else {
                return null;
            }
        }

        $closure($aux);
    }

    public function merge(array $updates): void
    {
        foreach ($updates as $value) {
            if ($value['id'] === 'saved') {
                continue;
            }

            //создание новых вложенных нод
            if (false !== strpos($value['id'], 'generate')) {
                $id = $this->getMaxId() + 1;

                $parent_id = $value['parent_id'];
                if (isset($this->created[$value['parent_id']]['id'])) {
                    $parent_id = $this->created[$value['parent_id']]['id'];
                }

                if (false !== $path = $this->findPathTo($parent_id)) {
                    $this->goto($path, function (&$node) use ($value, $id, $parent_id) {
                        if ($value['is_deleted']) {
                            $this->deleteNested($value['id'], $value['nested']);
                        }

                        if (isset($this->deleted[$parent_id])) {
                            $this->rejected[$value['id']] = ['id' => $value['id']];

                            return;
                        }

                        $node['nested'][] = [
                            'id' => $id,
                            'name' => $value['name'],
                            'is_deleted' => !$node['is_deleted'] ? $value['is_deleted'] : true,
                            'parent_id' => is_numeric($value['parent_id'])
                                ? $value['parent_id']
                                : $this->created[$value['parent_id']]['id'],
                        ];
                        $this->created[$value['id']] = ['id' => $id, 'parent_id' => $parent_id];
                    });

                    if (isset($value['nested']) && \is_array($value['nested'])) {
                        $this->merge($value['nested']);
                    }
                }
                //редактирование сущесвтующих нод
            } else if (false !== $path = $this->findPathTo($value['id'])) {
                $this->goto($path, function (&$node) use ($value) {
                    if (!$value['is_deleted']) {
                        $node['name'] = $value['name'];
                    }
                    if (!$node['is_deleted']) {
                        $node['is_deleted'] = $value['is_deleted'];
                    }
                    if ($value['is_deleted']) {
                        $this->deleteNested($value['id']);
                    }

                    if (isset($value['nested']) && \is_array($value['nested'])) {
                        $this->merge($value['nested']);
                    }
                });
            }
        }
    }

    private function deleteNested($id, $nested = null)
    {
        if (false !== $path = $this->findPathTo($id)) {
            $this->goto($path, function (&$node) use ($nested) {
                if (null === $nested && isset($node['nested'])) {
                    $nested = $node['nested'];
                }

                $node['is_deleted'] = true;
                $this->deleted[$node['id']] = ['id' => $node['id']];

                if (\is_array($nested)) {
                    foreach ($nested as $item) {
                        $this->deleted[$item['id']] = ['id' => $item['id']];
                        $this->deleteNested($item['id']);
                    }
                }
            });
        }
    }

    private function getMaxId(): int
    {
        $ids = [];
        array_walk_recursive($this->state, function ($item, $key) use (&$ids) {
            if ('id' === $key) {
                $ids[] = $item;
            }
        });
        sort($ids);

        return (int)array_pop($ids);
    }

    public function getCreated(): array
    {
        return $this->created;
    }

    public function getDeleted(array $cache_tree): array
    {
        foreach ($this->deleted as $item) {
            if (false === $this->findPathTo($item['id'], $cache_tree)) {
                unset($this->deleted[$item['id']]);
            }
        }

        return $this->deleted;
    }

    public function getRejected(): array
    {
        return $this->rejected;
    }
}
