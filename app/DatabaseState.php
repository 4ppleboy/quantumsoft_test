<?php

namespace app;

class DatabaseState
{
    private $state;
    private $created;

    public function __construct(array $state)
    {
        $this->state = $state;
        $this->created = [];
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
            } elseif ($value === $id) {
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
                $this->created[$value['id']] = ['id' => $id, 'parent_id' => $parent_id];

                if (false !== $path = $this->findPathTo($parent_id)) {
                    $this->goto($path, function (&$node) use ($value, $id) {
                        $node['nested'][] = [
                            'id' => $id,
                            'name' => $value['name'],
                            'is_deleted' => $value['is_deleted'],
                            'parent_id' => is_numeric($value['parent_id'])
                                ? $value['parent_id']
                                : $this->created[$value['parent_id']]['id'],
                        ];
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

                    $node['is_deleted'] = $value['is_deleted'];
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

    private function deleteNested($id)
    {
        if (false !== $path = $this->findPathTo($id)) {
            $this->goto($path, function (&$node) {
                $node['is_deleted'] = true;

                if (isset($node['nested']) && \is_array($node['nested'])) {
                    foreach ($node['nested'] as $item) {
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
}
