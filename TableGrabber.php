<?php
/**
* Parses html from a given url and grabs the data from specified tables on the page and
* provides utilities for saving and loading the tables.
* @author In order of contribution Daniel Church <daniellchurch@fvcc.edu>, Connor Mesec <connormesec@gmail.com>
* @copyright (c) 2017, Daniel Church, Connor Mesec
* @license https://opensource.org/licenses/lgpl-license.php LGPL
*/
class TableGrabber {
   /**
    * Grabs the full html of a page.
    * @param string $url The url of the page you want to grab.
    * @return string The html source of the requested url.
    */
   public static function grabFullPage(string $url): string {
       $curl = curl_init($url);
       curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

       $html = curl_exec($curl);

       if (curl_errno($curl)) {
           echo ('Scraper error: ') . curl_error($curl);
           exit;
       }

       curl_close($curl);

       return $html;
   }

   /**
    * Replace any ( / [ ] ^ $ . | ? * + ( ) { } )'s in the html that would break the regex expression.
    * @param string $tag The tag to prepare that may contain characters that need to be escaped.
    * @return string The correctly regex ready tag .
    */
   private static function prepTag(string $tag): string {
       // } { ) ( + * ? | . $ ^ ] [ /
       return trim(
               preg_replace('/\}/', '\\}',
               preg_replace('/\{/', '\\{',
               preg_replace('/\)/', '\\)',
               preg_replace('/\+/', '\\+',
               preg_replace('/\*/', '\\*',
               preg_replace('/\?/', '\\?',
               preg_replace('/\|/', '\\|',
               preg_replace('/\./', '\\.',
               preg_replace('/\$/', '\\$',
               preg_replace('/\^/', '\\^',
               preg_replace('/\]/', '\\]',
               preg_replace('/\[/', '\\[',
               preg_replace('/\//', '\\/', $tag
                       ))))))))))))));
   }

   /**
    * Grabs the html of a given url between two optional tags
    * @param string $url The url to grab the html from.
    * @param string $startTag The start of the area to grab, as an html tag (use id's, etc to specify which).
    * @param string $endTag The end of t he area to grab, as an html tag.
    * @param array linesToReplace Option values that can be passed in to be replaced with other values.  Use the format oldTag:newTag.
    * @return array The html of the given url.
    */
   public static function grabPage(string $url, string $startTag = '', string $endTag = '', Array $linesToReplace = array()): string {
       $html = TableGrabber::grabFullPage($url);

       // Put it all on one line
       $page = trim(preg_replace('/\n/', '', $html));
       
       foreach($linesToReplace as $line) {
           $split = explode(":", $line);
           $page = preg_replace($split[0], TableGrabber::prepTag($split[1]), $page);
       }

       $list = array();
       if (!preg_match('/' . TableGrabber::prepTag($startTag) . '(.*?)' . TableGrabber::prepTag($endTag) . '/', $page, $list))
           echo('Error');
       else
           return $list[0];
   }

   /**
    * Reads through a html file and returns all that data points that 'matter'
    * (ie the html between braces that have a start and end around them)
    * Ex. <html> <body> <p> Hello World </p> <a href = 'https://stackoverflow.com/'> Click Me </a> </body> </html>
    * Here, 'Hello World' and 'Click Me' are data points that matter
    * @param string $url The url to grab parse.
    * @param string $startTag The start of the area to grab, as an html tag (use id's, etc to specify which).
    * @param string $endTag The end of t he area to grab, as an html tag.
    * @param array linesToReplace Option values that can be passed in to be replaced with other values.  Use the format oldTag:newTag.
    * @param bool $mock If you're passing in mock html instead of a url for testing
    * @return string[] An array of all data one layer deep in html tags 
    */
   public static function parseHTML(string $url, string $startTag = '', string $endTag = '', Array $linesToReplace = array(), bool $mock = false): array {
       if(!$mock)
           $page = TableGrabber::grabPage ($url, $startTag, $endTag, $linesToReplace);
       else
           $page = $url;

       // Replace all newlines with nothing
       $html = trim(preg_replace('/\n/', '', $page));

       // Find all < ... >
       $data = array(array());
       preg_match_all('/>(.*?)</', $html, $data);

       // Remove any remaining <>'s
       $out = array();
       $index = 0;
       foreach($data[0] as &$row) {
           // If the line only contains '&nbsp;' or '<' or '>', don't add it to the table
           if(preg_match('/^\\s+$/', $row) || ($row = trim(preg_replace('/(<|>)|(&nbsp;)/', '', $row))) != '') {
               $out[$index++] = trim($row);
           }
       }

       return $out;
   }

   /**
    * Parses the data points out of a table from a given url between two given html tags
    * Use the two tags to specify which table
    * @param string $url The url to grab the html from.
    * @param string $startTag The start of the area to grab, as an html tag (use id's, etc to specify which).
    * @param string $endTag The end of t he area to grab, as an html tag.
    * @param int $rowWidth The width of a row of the table you're parsing
    * @param array linesToReplace Option values that can be passed in to be replaced with other values.  Use the format oldTag:newTag.
    * @param bool $mock If you're passing in mock html instead of a url for testing
    * @return string[][] A 2d array of all data one layer deep in html tags 
    */
   public static function parseTable(string $url, string $startTag, string $endTag, int $rowWidth, Array $linesToReplace = array(), bool $mock = false): array {
       $data = TableGrabber::parseHTML($url, $startTag, $endTag, $linesToReplace, $mock);

       $out = array(array());

       $rowIndex = 0;
       $colIndex = 0;

       // Puts the data into a 2d array based on the given rowWidth
       foreach($data as $cell) {
           $out[$rowIndex][$colIndex++] = $cell;
           if($colIndex >= $rowWidth) {
               $colIndex = 0;
               $rowIndex++;
           }
       }

       return $out;
   }

   /**
    * Renders a table returned from parseTable()
    * @param type $table The table returned from parseTable()
    */
   public static function renderTable(array $table):void {
       echo('<table width="98%" class="tablelines" cellpadding="2" border="0" cellspacing="1">');

       foreach($table as $row) {
           echo('<tr align="center" class="light">');
           foreach($row as $cell)
               echo ('<td>' . $cell . '</td>');
           echo('</tr>');
       }

       echo('</table>');
   }

   /**
    * Saves a given table to a given file in a CSV format with an optional delimiter.
    * @param string $fileName The name of the file to save to.
    * @param string[][] $table The table to save to file.
    * @param string $delimiter The delimiter to use (Default: '%').
    */
   public static function saveTable(string $fileName, array $table, string $delimiter = '%'): void {
       // Open the file with write permissions (can write to and create the file)
       $path = substr($fileName, 0, strrpos($fileName, '\\'));
       if (!file_exists($path)) {
           mkdir($path, 0777, true);
       }

       $statsFile = fopen($fileName, 'w+');

       // Write the data to the file
       foreach ($table as $column) {
           foreach ($column as $cell)
               fwrite($statsFile, trim($cell) . $delimiter);
           fwrite($statsFile, "\n");
       }
   }

   /**
    * Loads a table from a given file.
    * @param string $fileName The name of the file to load.
    * @param string $delimiter The delimiter used in the file (Default '%').
    * @return String[][] The table read from the file.
    */
   public static function loadTable(string $fileName, string $delimiter = '%'): array {
       // Open the file with read permissions (CANNOT GENERATE THE FILE)
       $statsFile = fopen($fileName, "r");

       // TODO: Edit this according to how we decide to update the page regularly
       // Get the date
       fgets($statsFile);

       $out = array(array());

       // Read the whole file
       $i = 0;
       while (!feof($statsFile)) {
           // Line by line
           $line = fgets($statsFile);

           // Skip blank lines
           if (trim($line) == '')
               continue;

           // Split the line into an array seperated by #
           $statVars = explode($delimiter, $line);

           for ($j = 0; $j < count($statVars); $j++)
               $out[$i][$j] = $statVars[$j];
           $i++;
       }
       // Close the file
       fclose($statsFile);

       return $out;
   }
}