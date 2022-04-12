<?php
final class KeyModel{
    public ?int $roomId;
    public ?int $employeeId;

    public function __construct(array $keyData = [])
    {
        $this->roomId = filter_input(INPUT_POST,'room',FILTER_VALIDATE_INT);
        $this->employeeId = filter_input(INPUT_POST,'employee',FILTER_VALIDATE_INT);
    }

    public static function readPostData(): KeyModel
    {
        return new self($_POST);
    }

    public function Insert():bool{
        $query = 'INSERT INTO `key` (room, employee) VALUES (:roomId, :employeeId)';
        $stmt = DB::getConnection()->prepare($query);
        $stmt->bindParam(':roomId',$this->roomId);
        $stmt->bindParam(':employeeId',$this->employeeId);

        if(!$stmt->execute())
        {
            return false;
        }
        $this->keyId = DB::getConnection()->lastInsertId();
        return true;
    }
    public function Delete():bool{
        $query = 'DELETE FROM `key` WHERE (employee =:employeeId) AND (room =:roomId)';
        $stmt = DB::getConnection()->prepare($query);
        $stmt->bindParam(':employeeId',$this->employeeId);
        $stmt->bindParam(':roomId',$this->roomId);
        return $stmt->execute();
    }
}