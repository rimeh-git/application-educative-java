<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GoogleCalendarService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $projectDir
    ) {}

    private function getAccessToken(): ?string
    {
        $tokenPath = $this->projectDir . '/config/token.json';
        if (!file_exists($tokenPath)) {
            return null;
        }
        $content = file_get_contents($tokenPath);
        if ($content === false) {
            return null;
        }
        /** @var array{access_token?: string}|null $token */
        $token = json_decode($content, true);
        return isset($token['access_token']) ? (string) $token['access_token'] : null;
    }

    /** @return array<mixed> */
    public function getUpcomingEvents(int $maxResults = 10): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) return [];

        try {
            $response = $this->httpClient->request('GET', 'https://www.googleapis.com/calendar/v3/calendars/primary/events', [
                'headers' => ['Authorization' => 'Bearer ' . $accessToken],
                'query'   => [
                    'maxResults'   => $maxResults,
                    'orderBy'      => 'startTime',
                    'singleEvents' => 'true',
                    'timeMin'      => (new \DateTime())->format('c'),
                ],
            ]);
            $data = $response->toArray();
            return $data['items'] ?? [];
        } catch (\Exception) {
            return [];
        }
    }

    /**
     * @param array<string, mixed> $eventData
     * @return array<string, bool|string|array<string, mixed>>
     */
    public function createEvent(array $eventData): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) return ['success' => false, 'error' => 'Non authentifie'];

        try {
            $startDate = $eventData['startDate'];
            $endDate   = $eventData['endDate'];
            $response  = $this->httpClient->request('POST', 'https://www.googleapis.com/calendar/v3/calendars/primary/events', [
                'headers' => ['Authorization' => 'Bearer ' . $accessToken, 'Content-Type' => 'application/json'],
                'json'    => [
                    'summary'     => $eventData['title'],
                    'description' => $eventData['description'] ?? '',
                    'start'       => ['dateTime' => $startDate instanceof \DateTimeInterface ? $startDate->format('c') : (string)$startDate, 'timeZone' => 'Africa/Tunis'],
                    'end'         => ['dateTime' => $endDate instanceof \DateTimeInterface ? $endDate->format('c') : (string)$endDate, 'timeZone' => 'Africa/Tunis'],
                    'conferenceData' => [
                        'createRequest' => [
                            'requestId' => uniqid('meet_', true),
                            'conferenceSolutionKey' => ['type' => 'hangoutsMeet']
                        ]
                    ]
                ],
                'query' => ['conferenceDataVersion' => 1]
            ]);
            $eventResult = $response->toArray();
            $meetLink = $eventResult['conferenceData']['entryPoints'][0]['uri'] ?? null;
            return ['success' => true, 'event' => $eventResult, 'meetLink' => $meetLink];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * @param array<string, mixed> $eventData
     * @return array<string, bool|string|array<string, mixed>>
     */
    public function updateEvent(string $eventId, array $eventData): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) return ['success' => false, 'error' => 'Non authentifie'];

        try {
            $startDate = $eventData['startDate'];
            $endDate   = $eventData['endDate'];
            $response  = $this->httpClient->request('PUT', 'https://www.googleapis.com/calendar/v3/calendars/primary/events/' . $eventId, [
                'headers' => ['Authorization' => 'Bearer ' . $accessToken, 'Content-Type' => 'application/json'],
                'json'    => [
                    'summary'     => $eventData['title'],
                    'description' => $eventData['description'] ?? '',
                    'start'       => ['dateTime' => $startDate instanceof \DateTimeInterface ? $startDate->format('c') : (string)$startDate, 'timeZone' => 'Africa/Tunis'],
                    'end'         => ['dateTime' => $endDate instanceof \DateTimeInterface ? $endDate->format('c') : (string)$endDate, 'timeZone' => 'Africa/Tunis'],
                ],
            ]);
            return ['success' => true, 'event' => $response->toArray()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** @return array<string, mixed> */
    public function deleteEvent(string $eventId): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) return ['success' => false, 'error' => 'Non authentifie'];

        try {
            $this->httpClient->request('DELETE', 'https://www.googleapis.com/calendar/v3/calendars/primary/events/' . $eventId, [
                'headers' => ['Authorization' => 'Bearer ' . $accessToken],
            ]);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** @return array<string, mixed> */
    public function getStatus(): array
    {
        $tokenPath = $this->projectDir . '/config/token.json';
        return ['configured' => file_exists($tokenPath) && $this->getAccessToken() !== null];
    }

    /** @return array<mixed> */
    public function getEmploiDuTempsSemaine(\DateTime $startDate): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) return [];

        $endDate = clone $startDate;
        $endDate->modify('+7 days');

        try {
            $response = $this->httpClient->request('GET', 'https://www.googleapis.com/calendar/v3/calendars/primary/events', [
                'headers' => ['Authorization' => 'Bearer ' . $accessToken],
                'query'   => [
                    'timeMin'      => $startDate->format('c'),
                    'timeMax'      => $endDate->format('c'),
                    'singleEvents' => 'true',
                    'orderBy'      => 'startTime',
                ],
            ]);
            $data = $response->toArray();
            return $data['items'] ?? [];
        } catch (\Exception) {
            return [];
        }
    }
}
