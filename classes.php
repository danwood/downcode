<?php

error_reporting(E_ALL);
date_default_timezone_set('America/Los_Angeles');

// Temporarily don't use $_SERVER['DOCUMENT_ROOT'] - based database.
define('DROPBOX_DIR',      dirname(__FILE__)  . '/../../Dropbox');
define('DROPBOX_RESOLVED', is_link(DROPBOX_DIR) ? readlink(DROPBOX_DIR) : DROPBOX_DIR);
include_once(DROPBOX_RESOLVED . '/downcode_db/secrets.php');    // $password

define('DOWNCODE_DBDIR',    DROPBOX_RESOLVED . '/downcode_db');
define('DOWNCODE_FILESDIR', DROPBOX_RESOLVED . '/downcode_files');

class DowncodeDB extends SQLite3
{
    function __construct()
    {
      $dbPath = DOWNCODE_DBDIR . '/downcode.sqlite3';
      $this->open($dbPath, SQLITE3_OPEN_READWRITE);
    }

    function backup()
    {
  		$dbPath = DOWNCODE_DBDIR . '/downcode.sqlite3';
  		$copyPath = DOWNCODE_DBDIR . '/backup.' . date('Y-m-d.G;i;s') . '.sqlite3';
  		copy($dbPath, $copyPath);
  		error_log("copy $dbPath to $copyPath");
    }

    function formats() {

      $result = Array();
      $query = 'select * from format order by ordering';
      $ret = $this->query($query);
      while ($row = $ret->fetchArray(SQLITE3_ASSOC) ){
        $entry = Array('description' => $row['description'], 'extension' => $row['extension']);
        $result[] = $entry;
      }
      return $result;
    }

}


/*
  $dbPath = DOWNCODE_DBDIR . '/downcode.sqlite3';
  $db = new SQLite3($dbPath) or die('Unable to open database');

  $query = 'select * from events';

  die;
  }
  while ($row = $ret->fetchArray(SQLITE3_ASSOC) ){
  $query = 'update events set ' . $propertyName . ' = ';
  $query .= "'" . SQLite3::escapeString($maxFilename) . "'";
  $query .= ' where id=' . $id;
   while ($row = $ret->fetchArray(SQLITE3_ASSOC) ){

       $event = new Event($row);       // Copy the event, work with that.

               $value = datetimeTo8601($value);
           }
           $query .= "'" . SQLite3::escapeString($value) . "',";
       }
   }

           $valuesList .= "'" . SQLite3::escapeString($value) . "',";



    $valuesList = '';
    $query = 'insert into events (';
    foreach ($inputs as $key => $value) {
        if (!endswith($key, '_time')) {
            $query .= $key . ',';

            if (endswith($key, 'Date')) {
                $value = dateTo8601($value);
            } else if (endswith($key, 'DateTime') || endswith(substr($key,0,-1), 'DateTime')) {
                $value = datetimeTo8601($value);
            }

            $valuesList .= "'" . SQLite3::escapeString($value) . "',";
        }
    }
    $query = substr($query, 0, -1); // take out last ,
    $query .= ') values(';
    $query .= $valuesList;
    $query = substr($query, 0, -1); // take out last ,
    $query .= ')';

    $ret = $db->query($query);
    if(!$ret) {
        echo $db->lastErrorMsg();
        die;
    }
    $id = $db->lastInsertRowID();

*/