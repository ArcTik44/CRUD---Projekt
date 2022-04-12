<?php
final class RoomModel
{
    public ?int $room_id;
    public string $name;
    public string $no;
    public ?string $phone;
    private array $validationErrors = [];

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function __construct(array $roomData = [])
    {
        $id = $roomData['room_id']??null;
        if (is_string($id)) {
            $id = filter_var($id, FILTER_VALIDATE_INT);
        }
        $this->room_id = $id;
        $this->name = filter_input(INPUT_POST, 'name')??'';
        $this->no = filter_input(INPUT_POST,'no',FILTER_VALIDATE_INT)??'';
        $this->phone = filter_input(INPUT_POST,'phone',FILTER_VALIDATE_INT) ?? null;
    }

    public function Validate():bool
    {
        $isOk = true;
        if (!$this->name) {
            $isOk = false;
            $this->validationErrors['name'] = "Name cannot be empty";
        }
        if (!$this->no) {
            $isOk = false;
            $this->validationErrors['no'] = "Number cannot be empty";
        }
        if (!$this->phone) {
            $this->phone = null;
        }
        return $isOk;
    }

    public function Insert(): bool
    {
        $query = "INSERT INTO room (name, no, phone) VALUES (:name, :no , :phone)";
        $stmt = DB::getConnection()->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':no', $this->no);
        $stmt->bindParam(':phone', $this->phone);
       
        if (!$stmt->execute())
            return false;

        $this->room_id = DB::getConnection()->lastInsertId();
        return true;
    }

    public function Update(): bool
    {
        $query = "UPDATE room SET name=:name, no=:no, phone=:phone WHERE room_id=:room_id";

        $stmt = DB::getConnection()->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':no', $this->no);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':room_id', $this->room_id);

        return $stmt->execute();
    }
    public function Delete(): bool
    {
        $query = "DELETE FROM room WHERE room_id=:room_id";
        $stmt = DB::getConnection()->prepare($query);
        $stmt->bindParam(':roomId', $this->room_id);
        return $stmt->execute();
    }
    public static function findById(int $room_id): ?RoomModel
    {
        $query = "SELECT * FROM room WHERE room_id=:roomId";
        $stmt = DB::getConnection()->prepare($query);
        $stmt->bindParam(':roomId', $room_id);

        $stmt->execute();
        $dbData = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$dbData) {
            return null;
        }
        return new self($dbData);
    }

    public static function readPostData(): RoomModel
    {
        return new self($_POST);
    }
}