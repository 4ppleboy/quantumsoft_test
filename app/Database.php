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
                'nested' => [
                    0 => [
                        'id' => 2,
                        'name' => 'node2',
                    ],
                    1 => [
                        'id' => 3,
                        'name' => 'node3',
                        'nested' => [
                            0 => [
                                'id' => 5,
                                'name' => 'node4',
                            ],
                            1 => [
                                'id' => 6,
                                'name' => 'node5',
                            ]
                        ]
                    ],
                    2 => [
                        'id' => 4,
                        'name' => 'node6',
                        'nested' => [
                            0 => [
                                'id' => 7,
                                'name' => 'node7',
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
        unlink($this->state_path . $this->state_file);
    }
}
