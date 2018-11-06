<?php

namespace App;

class Database
{
    private $state_path = __DIR__ . '/state/';
    protected $state_file = 'nested_tree.db';

    public function getData(): array
    {
        if (file_exists($this->state_path . $this->state_file)) {
            return json_decode(file_get_contents($this->state_path . $this->state_file), true);
        }

        return [
            0 => [
                'id' => 1,
                'name' => 'node1',
                'parent_id' => null,
                'nested' => [
                    0 => [
                        'id' => 2,
                        'name' => 'node2',
                        'parent_id' => 1,
                    ],
                    1 => [
                        'id' => 3,
                        'name' => 'node3',
                        'parent_id' => 1,
                        'nested' => [
                            0 => [
                                'id' => 5,
                                'name' => 'node4',
                                'parent_id' => 3,
                                'nested' => [
                                    0 => [
                                        'id' => 8,
                                        'name' => 'node5',
                                        'parent_id' => 5,
                                        'nested' => [
                                            0 => [
                                                'id' => 10,
                                                'name' => 'node6',
                                                'parent_id' => 8,
                                            ],
                                        ]
                                    ],
                                    1 => [
                                        'id' => 9,
                                        'name' => 'node7',
                                        'parent_id' => 5,
                                    ]
                                ]
                            ],
                            1 => [
                                'id' => 6,
                                'name' => 'node8',
                                'parent_id' => 3,
                                'nested' => [
                                    0 => [
                                        'id' => 11,
                                        'name' => 'node11',
                                        'parent_id' => 6,
                                        'nested' => [
                                            0 => [
                                                'id' => 12,
                                                'name' => 'node12',
                                                'parent_id' => 11,
                                            ],
                                        ]
                                    ],
                                ]
                            ]
                        ]
                    ],
                    2 => [
                        'id' => 4,
                        'name' => 'node9',
                        'parent_id' => 1,
                        'nested' => [
                            0 => [
                                'id' => 7,
                                'name' => 'node10',
                                'parent_id' => 4,
                            ]
                        ]
                    ],
                ]
            ]
        ];
    }

    public function apply($state): void
    {
        file_put_contents($this->state_path . $this->state_file, json_encode($state));
    }

    public function fallback(): void
    {
        @unlink($this->state_path . $this->state_file);
    }
}
