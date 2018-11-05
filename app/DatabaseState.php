<?php

namespace app;

class DatabaseState
{
    private $state;

    public function __construct(array $state)
    {
        $this->state = $state;
    }

    public function get(): array
    {
        return $this->state;
    }

    public function findPathTo(int $id, array $haystack = null, array &$path = [])
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
            if ($value['id'] === 'generate') {
                $id = $this->getMaxId() + 1;
                if (false !== $path = $this->findPathTo($value['parent_id'])) {
                    $this->goto($path, function (&$node) use ($value, $id) {
                        $node['nested'][] = [
                            'id' => $id,
                            'name' => $value['name'],
                            'is_deleted' => $value['is_deleted'],
                            'parent_id' => $value['parent_id'],
                        ];
                    });
                }
            } else if ($value['id'] === 'saved') {
                continue;
            } else if (false !== $path = $this->findPathTo($value['id'])) {
                $this->goto($path, function (&$node) use ($value) {
                    $node['name'] = $value['name'];
                    $node['is_deleted'] = $value['is_deleted'];

                    if (isset($value['nested']) && \is_array($value['nested'])) {
                        $this->merge($value['nested']);
                    }
                });
            }
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
}
