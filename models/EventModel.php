<?php

require_once __DIR__ . '/../config/database.php';

class EventModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getEvents()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM events ORDER BY PublicationDate DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Failed to fetch banner events: ' . $e->getMessage());
        }
    }

    public function createEvent($eventData)
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("INSERT INTO events (Title, PublicationDate, StartDate, EndDate, Description, ShortDescription, Image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $eventData['title'],
                $eventData['publicationDate'],
                $eventData['startDate'],
                $eventData['endDate'],
                $eventData['description'],
                $eventData['shortDescription'],
                $eventData['image']
            ]);

            $this->db->commit();
            return ['id' => $this->db->lastInsertId()] + $eventData;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception('Failed to create event: ' . $e->getMessage());
        }
    }

    public function deleteEventsById($eventId)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM events WHERE id = ?");
            $stmt->execute([$eventId]);

            if ($stmt->rowCount() > 0) {
                return ['message' => 'deleted successfully'];
            } else {
                return ['error' => 'Not found'];
            }
        } catch (Exception $e) {
            throw new Exception('Failed to delete: ' . $e->getMessage());
        }
    }

    public function updateEvent($eventId, $updatedData)
    {
        if (!isset($updatedData['Title'], $updatedData['PublicationDate'], $updatedData['Description'])) {
            throw new Exception('Missing required fields');
        }

        try {

            $stmt = $this->db->prepare("UPDATE events SET title = ?, publicationDate = ?, startDate = ?, endDate = ?, description = ?, shortDescription = ? , image = ? WHERE ProductID = ?");

            $stmt->execute([
                $updatedData['title'],
                $updatedData['publicationDate'],
                $updatedData['startDate'],
                $updatedData['endDate'],
                $updatedData['description'],
                $updatedData['shortDescription'],
                $updatedData['image'],
                $eventId
            ]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Not found or no changes made');
            }

            return ['success' => true, 'message' => 'Event updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update event: ' . $e->getMessage()];
        }
    }

}
