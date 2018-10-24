<?php

namespace App;

class DB
{
    public $data;

    public function __construct()
    {
        //Результат выборки из некой базы со сформированными путями к каждому элементу
        $this->data = [
            0 => [
                'name' => 'node1',
                'path' => [0],
                'nested' => [
                    0 => [
                        'name' => 'node2',
                        'path' => [0, 0],
                        'nested' => []
                    ],
                    1 => [
                        'name' => 'node3',
                        'path' => [0, 1],
                        'nested' => [
                            0 => [
                                'name' => 'node4',
                                'path' => [0, 1, 0],
                                'nested' => []
                            ],
                            1 => [
                                'name' => 'node5',
                                'path' => [0, 1, 1],
                                'nested' => []
                            ]
                        ]
                    ],
                    2 => [
                        'name' => 'node6',
                        'path' => [0, 2],
                        'nested' => [
                            0 => [
                                'name' => 'node7',
                                'path' => [0, 2, 0],
                                'nested' => []
                            ]
                        ]
                    ],
                ]
            ]
        ];
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getNode(string $path): ?array
    {
        $node = null;
        $sequence = self::preparePath($path);
        $sequence_length = \count($sequence);
        $aux =& $this->data;
        foreach ($sequence as $iterator => $index) {
            if (isset($aux[$index])) {
                $aux =& $aux[$index];
            } else {
                return null;
            }
            if ($sequence_length === $iterator + 1 && isset($aux['name'], $aux['path'])) {
                $node = [
                    'name' => $aux['name'],
                    'path' => $aux['path']
                ];
            }
        }

        return $node;
    }

    public function goto(string $path, \Closure $closure)
    {
        $sequence = self::preparePath($path);
        $aux =& $this->data;
        foreach ($sequence as $index) {
            if (isset($aux[$index])) {
                $aux =& $aux[$index];
            } else {
                return null;
            }
        }

        $closure($aux);
    }

    public static function preparePath(string $path): array
    {
        $sequence = explode(',', $path);

        $i = 0;
        do {
            if ($i % 2 | 0) {
                array_splice($sequence, $i, 0, 'nested');
            }
            $i++;
        } while (null !== $sequence[$i]);

        return $sequence;
    }
}
