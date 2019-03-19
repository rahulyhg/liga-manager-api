<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL;

use HexagonalPlayground\Tests\Framework\SlimClient;

class Client
{
    /** @var SlimClient */
    private $slimClient;

    /** @var array */
    private $headers;

    /**
     * @param SlimClient $slimClient
     */
    public function __construct(SlimClient $slimClient)
    {
        $this->slimClient = $slimClient;
        $this->headers    = [];
    }

    public function useCredentials(string $email, string $password)
    {
        $this->headers['Authorization'] = 'Basic ' . base64_encode($email . ':' . $password);
    }

    public function clearAuth(): void
    {
        unset($this->headers['Authorization']);
    }

    public function getAllSeasons(): array
    {
        return $this->request(<<<GRAPHQL
    query allSeasons {
      allSeasons {
        id,
        name,
        created_at
      }
    }
GRAPHQL
);
    }

    public function createPitch($id, $label, $latitude, $longitude): void
    {
        $query = <<<'GRAPHQL'
mutation createPitch($id: String, $label: String!, $longitude: Float!, $latitude: Float!) {
  createPitch(id: $id, label: $label, longitude: $longitude, latitude: $latitude)
}
GRAPHQL;

        $this->request($query, [
            'id'        => $id,
            'label'     => $label,
            'longitude' => $longitude,
            'latitude'  => $latitude
        ]);
    }

    public function getPitchById($id): ?\stdClass
    {
        $query = <<<'GRAPHQL'
query pitch($id: String!) {
  pitch(id: $id) {
    id,
    label,
    location_latitude,
    location_longitude,
    contact {
      first_name,
      last_name,
      phone,
      email
    }
  }
}
GRAPHQL;

        $data = $this->request($query, ['id' => $id]);
        return $data->pitch ?? null;
    }

    public function updatePitchContact($pitchId, $contact): void
    {
        $query = <<<'GRAPHQL'
mutation updatePitchContact($pitchId: String!, $firstName: String!, $lastName: String!, $phone: String!, $email: String!) {
  updatePitchContact(pitch_id: $pitchId, first_name: $firstName, last_name: $lastName, phone: $phone, email: $email)
}
GRAPHQL;

        $this->request($query, [
            'pitchId' => $pitchId,
            'firstName' => $contact['first_name'],
            'lastName' => $contact['last_name'],
            'phone' => $contact['phone'],
            'email' => $contact['email']
        ]);
    }

    public function deletePitch($pitchId): void
    {
        $query = <<<'GRAPHQL'
mutation deletePitch($pitchId: String!) {
  deletePitch(pitch_id: $pitchId)
}
GRAPHQL;

        $this->request($query, [
            'pitchId' => $pitchId
        ]);
    }

    public function createTeam($id, $name): void
    {
        $query = <<<'GRAPHQL'
mutation createTeam($id: String!, $name: String!) {
  createTeam(id: $id, name: $name)
}
GRAPHQL;

        $this->request($query, [
            'id'   => $id,
            'name' => $name
        ]);
    }

    public function getTeamById($id): ?\stdClass
    {
        $query = <<<'GRAPHQL'
query team($id: String!) {
  team(id: $id) {
    id,
    name,
    created_at,
    contact {
      first_name,
      last_name,
      phone,
      email
    }
  }
}
GRAPHQL;

        $data = $this->request($query, ['id' => $id]);
        return $data->team ?? null;
    }

    public function renameTeam($teamId, $newName): void
    {
        $query = <<<'GRAPHQL'
mutation renameTeam($teamId: String!, $newName: String!) {
  renameTeam(team_id: $teamId, new_name: $newName)
}
GRAPHQL;

        $this->request($query, [
            'teamId' => $teamId,
            'newName' => $newName
        ]);
    }

    public function updateTeamContact($teamId, $contact): void
    {
        $query = <<<'GRAPHQL'
mutation updateTeamContact($teamId: String!, $firstName: String!, $lastName: String!, $phone: String!, $email: String!) {
  updateTeamContact(team_id: $teamId, first_name: $firstName, last_name: $lastName, phone: $phone, email: $email)
}
GRAPHQL;

        $this->request($query, [
            'teamId' => $teamId,
            'firstName' => $contact['first_name'],
            'lastName' => $contact['last_name'],
            'phone' => $contact['phone'],
            'email' => $contact['email']
        ]);
    }

    public function deleteTeam($teamId): void
    {
        $query = <<<'GRAPHQL'
mutation deleteTeam($teamId: String!) {
  deleteTeam(team_id: $teamId)
}
GRAPHQL;

        $this->request($query, [
            'teamId' => $teamId
        ]);
    }

    public function getAllTeams(): array
    {
        $query = <<<GRAPHQL
    query allTeams {
      allTeams {
        id,
        name,
        created_at,
        contact {
          first_name,
          last_name,
          phone,
          email
        }
      }
    }
GRAPHQL;
        $data = $this->request($query);
        return $data->allTeams;
    }

    private function request(string $query, array $variables = [])
    {
        $response = $this->slimClient->post(
            '/api/graphql',
            ['query' => $query, 'variables' => $variables],
            $this->headers
        );

        $body = json_decode($response->getBody()->__toString());
        if (isset($body->errors) && is_array($body->errors) && count($body->errors) > 0) {
            throw new Exception($body->errors);
        }
        return $body->data;
    }
}