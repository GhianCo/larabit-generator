<?php

namespace App\Factory;

class LarabitGeneratorService
{
    private $dbConn;

    private $allTables = array();

    public function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
    }

    public function generateStructure($database)
    {
        $hasDatabase = $this->validateHasDatabase($database);
        if (!$hasDatabase) {
            throw new \Exception('La base de datos ' . $database . ' no existe.');
        }

        $this->allTables = $this->getTablesWithData($database);

    }

    function validateHasDatabase($database)
    {
        // the query to perform to get all the tables
        $dbs = $this->dbConn->query('SHOW DATABASES');
        $databases = array();
        while (($db = $dbs->fetchColumn(0)) !== false) {
            $databases[] = $db;
        }

        return in_array($database, $databases);
    }

    function getTablesWithData($database)
    {
        // the query to perform to get all the tables
        $this->dbConn->query('USE ' . $database);
        $query = "SHOW TABLES";
        $dbs = $this->doQuery($query, 'Tables_in_' . $database);
        // get the table listings collected and generated in the query
        foreach ($dbs as $key => $db) {
            // DESCRIBE query, gets all the field in a table
            $tableInfoQuery = "DESCRIBE " . $key;
            // perform the query having Field as key
            $dbQuery = $this->doQuery($tableInfoQuery, 'Field');

            // the field list array
            $fieldList = array();

            // array pointer
            $i = 0;
            // loop through the generated field list
            foreach ($dbQuery as $fKey => $fVal) {
                $data = new \stdClass();
                $data->key = $fKey;
                $data->data = $fVal;
                // put it into the array
                $fieldList[$i] = $data;
                $i++;
            }
            // after completing the loop, put the results into the table list array
            $tableList[$key] = $fieldList;
        }

        // put the final value into the class variable $tableListings
        $this->tableListings = $tableList;

        // returned value
        return $tableList;
    }

    function doQuery($sqlStatment, $indexField = 'auto')
    {
        // perform the query and put it into a temporary variable
        $dbQuery = $this->dbConn->query($sqlStatment);
        // create an array of queried objects
        $dataSet = array();

        // this variable is used for automatic counter
        $i = 0;

        // Structuring the internal table for the data
        // loop through the records retrieved
        while ($rows = $dbQuery->fetch()) {

            if ($indexField != 'auto') {                    // if not automatic indexing

                // use the specified indexField specified
                // as index pointer and assign the value retrived from the database
                $dataSet[$rows[$indexField]] = $rows;

            } else {                                        // if automatic indexing
                // assign current index count as a pointer for the current
                // data retrived form the databse.
                $dataSet[$i] = $rows;
                // increase index counter
                $i++;
            }

        }

        return $dataSet;
    }


}
