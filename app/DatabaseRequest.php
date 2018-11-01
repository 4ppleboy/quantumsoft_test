<?php

namespace App;

class DatabaseRequest
{
    private $database;
    private $databaseState;

    public function __construct(DatabaseState $databaseState)
    {
        $this->databaseState = $databaseState;
    }

    public function setDatabase(Database $database)
    {
        $this->database = $database;

        return $this;
    }

    public function getNode(?int $id): void
    {
        if (null === $id) {
            http_response_code(404);

            return;
        }

        $sequence = $this->databaseState->findPathTo($id);
        if (null === $node = $this->databaseState->getNode($sequence)) {
            http_response_code(404);
        }

        $sequence_length = \count($sequence);
        $aux =& $node['path'];
        foreach ($sequence as $iterator => $step_in) {
            if (is_numeric($step_in)) {
                $aux[$step_in] = [
                    'name' => 'unknown',
                ];
                if ($sequence_length === $iterator + 1 && isset($node['id'], $node['name'])) {
                    $aux[$step_in] = [
                        'name' => $node['name'],
                        'id' => $node['id']
                    ];
                    if (isset($node['is_deleted'])) {
                        $aux[$step_in]['is_deleted'] = $node['is_deleted'];
                    }
                }
            }
            unset($node['path'][$iterator + 1]);
            $aux =& $aux[$step_in];
        }

        echo json_encode($node['path']);
    }

    public function save(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $this->databaseState->merge($data['cache']);

        $state = $this->databaseState->get();
        $this->database->apply($state);

        http_response_code(200);
        echo json_encode($state);
    }

    public function fallback(): void
    {
        $this->database->fallback();

        http_response_code(204);
    }
}