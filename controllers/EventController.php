<?php

require_once __DIR__ . '/../models/EventModel.php';

class EventController
{
    private $eventModel;

    public function __construct($db)
    {
        $this->eventModel = new EventModel($db);
    }

    public function getEvents()
    {
        try {
            $events = $this->eventModel->getEvents();

            $processedEvents = array_map(function ($event) {
                if (isset($event['image']) && $event['image']) {
                    $event['image'] = base64_encode($event['image']);
                }
                return $event;
            }, $events);

            return $processedEvents;
        } catch (Exception $error) {
            error_log("Error fetching events: " . $error->getMessage());
            return ['error' => 'Failed to fetch events: ' . $error->getMessage()];
        }
    }

    public function updateEvents($eventId)
    {
        $updatedData = json_decode(file_get_contents("php://input"), true);

        error_log(print_r($updatedData, true));

        if ($updatedData === null) {
            return ['success' => false, 'message' => 'Invalid or empty data received'];
        }

        try {
            $updatedEvent = $this->eventModel->updateEvent($eventId, $updatedData);
            return $updatedEvent;
        } catch (Exception $e) {
            http_response_code(500);
            return ['success' => false, 'message' => 'Failed to update event: ' . $e->getMessage()];
        }
    }

    public function createEvent($data)
    {
        try {
            return $this->eventModel->createEvent($data);
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function deleteEventById($eventId)
    {
        $result = $this->eventModel->deleteEventsById($eventId);
        return $result;
    }
}
