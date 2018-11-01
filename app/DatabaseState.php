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
        $node = null;
        $sequence_length = \count($sequence);
        $aux =& $this->state;
        foreach ($sequence as $iterator => $index) {
            if (isset($aux[$index])) {
                $aux =& $aux[$index];
            } else {
                return null;
            }
            if ($sequence_length === $iterator + 1 && isset($aux['id'], $aux['name'])) {
                $node = [
                    'id' => $aux['id'],
                    'name' => $aux['name'],
                    'path' => $sequence,
                ];
                if (isset($aux['is_deleted'])) {
                    $node['is_deleted'] = $aux['is_deleted'];
                }
            }
        }

        return $node;
    }

    public function merge(array $updates, array &$state = null): void
    {
        if ($state === null) {
            $state =& $this->state;
        }

        foreach ($updates as $index => $value) {
            if (!isset($state[$index])) {
                if (\is_array($value)) {
                    array_walk_recursive($value, function (&$item, $key) {
                        if ($key === 'id' && $item === 'generate') {
                            $id = $this->getMaxId() + 1;
                            $item = $id;
                        }
                    });
                }
                $state[$index] = $value;

                continue;
            }

            if ($state[$index] !== $value) {
                if (\is_array($value)) {
                    $this->merge($value, $state[$index]);
                } else if ($value !== 'unknown') {
                    if ($index === 'id' && $value === 'generate') {
                        $id = $this->getMaxId() + 1;
                        $state[$index] = $id;
                    } else {
                        $state[$index] = $value;
                    }
                }
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
