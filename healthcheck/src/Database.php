<?php
namespace WPObsoflo;

class Database
{
    public static function connect()
    {
        return new \mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    }

    public static function query($query)
    {
        $result = self::query_all($query);

        if (count($result) > 0) {
            return self::query_all($query)[0];
        }
        return null;
    }

    public static function query_all($query)
    {
        $conn = self::connect();
        $results = [];
        $statement = $conn->query($query);
        while ($row = $statement->fetch_row()) {
            $results[] = $row;
        }
        $statement->close();
        $conn->close();
        return $results;
    }
}
