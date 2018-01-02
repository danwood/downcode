<?php

error_reporting(E_ALL);
date_default_timezone_set('America/Los_Angeles');

// Temporarily don't use $_SERVER['DOCUMENT_ROOT'] - based database.
define('DROPBOX_DIR',      dirname(__FILE__)  . '/../../Dropbox');
define('DROPBOX_RESOLVED', is_link(DROPBOX_DIR) ? readlink(DROPBOX_DIR) : DROPBOX_DIR);
include_once(DROPBOX_RESOLVED . '/downcode_db/secrets.php');    // $password

define('DOWNCODE_DBDIR',    DROPBOX_RESOLVED . '/downcode_db');
define('DOWNCODE_FILESDIR', DROPBOX_RESOLVED . '/downcode_files');

// Adapted from https://github.com/dflydev/dflydev-base32-crockford (MIT license)

/**
 * Base32 Crockford implementation
 *
 * @author Beau Simensen <beau@dflydev.com>
 */
class Crockford
{
    const NORMALIZE_ERRMODE_SILENT = 0;
    const NORMALIZE_ERRMODE_EXCEPTION = 1;

    private $symbols = array();
    private $flippedSymbols = array();
    private $alphabet = '';

    function __construct($alphabet)
    {
      // Constraint: I's and L's and O's in the alphabet not allowed since those get converted to 1's and 0's.
      if (preg_match('/[a-zILO]/', $alphabet)) {
        throw new \RuntimeException("Alphabet '$alphabet' cannot contain lowercase letters, I's, L's, or O's.");
      }

      $this->alphabet = $alphabet;
      $this->symbols = str_split($alphabet);
      $this->flippedSymbols = array();
      $counter = 0;
      $alphabetPlusChecksum = $alphabet . '*~$=@';  // to make the base-37 checksum thing work. Note we use @ not U
      $moreSymbols = str_split($alphabetPlusChecksum);
      foreach ($moreSymbols as $char) {
        $this->flippedSymbols[$char] = $counter++;
      }
    }

/*
    public static $symbols = array(
        '0', '1', '2', '3', '4',
        '5', '6', '7', '8', '9',
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',
        'J', 'K', 'M', 'N', 'P', 'Q', 'R', 'S',
        'T', 'V', 'W', 'X', 'Y', 'Z',
        '*', '~', '$', '=', 'U',
    );

    public static $flippedSymbols = array(
        '0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4,
        '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9,
        'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13,
        'E' => 14, 'F' => 15, 'G' => 16, 'H' => 17,
        'J' => 18, 'K' => 19, 'M' => 20, 'N' => 21,
        'P' => 22, 'Q' => 23, 'R' => 24, 'S' => 25,
        'T' => 26, 'V' => 27, 'W' => 28, 'X' => 29,
        'Y' => 30, 'Z' => 31,
        '*' => 32, '~' => 33, '$' => 34, '=' => 35, 'U' => 36,
    );
*/
    /**
     * Encode a number
     *
     * @param int $number
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function encode($number)
    {
        if (!is_numeric($number)) {
            throw new \RuntimeException("Specified number '{$number}' is not numeric");
        }

        if (!$number) {
            return 0;
        }

        $response = array();
        while ($number) {
            $remainder = $number % 32;
            $number = (int) ($number/32);
            $response[] = $this->symbols[$remainder];
        }

        return implode('', array_reverse($response));
    }

    /**
     * Encode a number with checksum
     *
     * @param int $number
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function encodeWithChecksum($number)
    {
        $encoded = $this->encode($number);

        return $encoded . $this->symbols[$number % 37];
    }

    /**
     * Decode a string
     *
     * @param string $string  Encoded string
     * @param int    $errmode Error mode
     *
     * @return int
     *
     * @throws \RuntimeException
     */
    public function decode($string, $errmode = self::NORMALIZE_ERRMODE_SILENT)
    {
        return $this->internalDecode($string, $errmode);
    }

    /**
     * Decode a string with checksum
     *
     * @param string $string  Encoded string
     * @param int    $errmode Error mode
     *
     * @return int
     *
     * @throws \RuntimeException
     */
    public function decodeWithChecksum($string, $errmode = self::NORMALIZE_ERRMODE_SILENT)
    {
        $checksum = substr($string, (strlen($string) -1), 1);
        $string = substr($string, 0, strlen($string) - 1);

        $value = $this->internalDecode($string, $errmode);
        $checksumValue = $this->internalDecode($checksum, self::NORMALIZE_ERRMODE_EXCEPTION, true);

        if ($checksumValue !== ($value % 37)) {
            throw new \RuntimeException("Checksum symbol '$checksum' is not correct value for '$string'");
        }

        return $value;
    }

    /**
     * Normalize a string
     *
     * @param string $string  Encoded string
     * @param int    $errmode Error mode
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function normalize($string, $errmode = self::NORMALIZE_ERRMODE_SILENT)
    {
        $origString = $string;

        $string = strtoupper($string);
        if ($string !== $origString && $errmode) {
            throw new \RuntimeException("String '$origString' requires normalization");
        }

        $string = str_replace('-', '', strtr($string, 'IiLlOo', '111100'));
        if ($string !== $origString && $errmode) {
            throw new \RuntimeException("String '$origString' requires normalization");
        }

        return $string;
    }

    /**
     * Decode a string
     *
     * @param string $string     Encoded string
     * @param int    $errmode    Error mode
     * @param bool   $isChecksum Is encoded with a checksum?
     *
     * @return int
     *
     * @throws \RuntimeException
     */
    protected function internalDecode($string, $errmode = self::NORMALIZE_ERRMODE_SILENT, $isChecksum = false)
    {
        if ('' === $string) {
            return '';
        }

        if (null === $string) {
            return '';
        }

        $string = $this->normalize($string, $errmode);

        if ($isChecksum) {
            $valid = '/^[' . $this->alphabet . '\*\~\$=@]+$/';
        } else {
            $valid = '/^[' . $this->alphabet . ']+$/';
        }

        if (!preg_match($valid, $string)) {
            throw new \RuntimeException("String '$string' contains invalid characters");
        }

        $total = 0;
        foreach (str_split($string) as $symbol) {

            if (isset($this->flippedSymbols[$symbol])) {              // Double check that it will find character
              $total = $total * 32 + $this->flippedSymbols[$symbol];
            }
        }

        return $total;
    }
}



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

  function findAlbumFromCode($code) {

    $result = Array();
    $statement = $this->prepare('SELECT * FROM album WHERE prefix = :prefix;');
    $statement->bindValue(':prefix', substr($code, 0, 1));
    $ret = $statement->execute();
    if ($result = $ret->fetchArray(SQLITE3_ASSOC) ){
      // We found an album that matches this prefix.
      $restOfCode = substr($code, 2);                 // Skip first TWO characters. Second is marketing code.
      $base32Converter = new Crockford($result['alphabet32']);
      $decoded = $base32Converter->decode($restOfCode);
      error_log('Decoded number from code ' . $restOfCode . ' = ' . $decoded);
      $modulo = $decoded % $result['seed'];
      error_log('Modulo seed ' . $result['seed'] . ' = ' . $modulo);
      if (0 == $modulo) {
        return $result;     // return the album!
      }
    }
    return null;
  }

/*
Looks like we can generate about 15,000 8-character codes (6 characters is a number, which is 30 bits, where we are multiplying by almost 2^16, which leaves about 2^14 so that makes sense.  If we had a higher seed like 2^18 then that would leave 2^12 codes which is about 4000.  If we had 9-character codes that would be another 5 bits, so 2^35 / 2^16 = 2^19 which would be > 500K codes available in that space!
 */
  function generateCodes($secondChar)      // Private for command line use
  {
    if (strlen($secondChar) != 1) { error_log("second character must be 1 alpha character"); return; }
    $statement = $this->prepare('SELECT * FROM album');
    $ret = $statement->execute();
    while ($result = $ret->fetchArray(SQLITE3_ASSOC) ){
      echo $result['title'] . PHP_EOL . PHP_EOL;
      $base32Converter = new Crockford($result['alphabet32']);
      $counter = 0;
      $seed = $result['seed'];
      $prefix = $result['prefix'];
      for ($i = 1 ; $i < 100000 ; $i++) {
        $fullCode = $prefix . $secondChar . $base32Converter->encode($i * $seed );
        if (strlen($fullCode) < 8) continue;
        if (strlen($fullCode) > 8) break;
        echo /* 'For basis ' . $i . ' -> ' . $i * $seed . ' : ' . */ $fullCode /* . ' ===== ' . $base32Converter->decode(substr($fullCode, 2)) */ . PHP_EOL;
        $counter++;
      }
      echo PHP_EOL . 'CODES GENERATED: ' . $counter . PHP_EOL;
    }
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