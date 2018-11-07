<?php

namespace App;

class DatabaseRequest
{
    /**
     * @var Database
     */
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

        echo json_encode([
            'id' => $node['id'],
            'name' => $node['name'],
            'is_deleted' => $node['is_deleted'] ?? false,
            'parent_id' => $node['parent_id'] ?? null,
        ]);
    }

    public function save(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $this->databaseState->merge($data['cache']);

        $state = $this->databaseState->get();
        $this->database->apply($state);

        http_response_code(200);
        echo json_encode([
            'db_tree' => $state,
            'created' => $this->databaseState->getCreated(),
            'deleted' => $this->databaseState->getDeleted($data['cache'])
        ]);
    }

    public function fallback(): void
    {
        $this->database->fallback();

        http_response_code(204);
    }
}