<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
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
             * @return array The html of the given url.
             */
            public static function grabPage(string $url, string $startTag = '', string $endTag = ''): string {
                $html = TableGrabber::grabFullPage($url);
                
                // Put it all on one line
                $page = trim(preg_replace('/\n/', '', $html));

                // TODO: decide how to handle these
                // Ideas: pass in array of values to skip
                //$page = preg_replace('/<td class="text-right">\\s+\\d+\\s+<\/td>/', '<td class="text-right"></td>', $page);
                //$page = preg_replace('/<th class="span2"><\/th>/', '<td>Boxscore</td>', $page);
                //$page = preg_replace('/<td>Boxscore<\\/td>/', '<td></td>', $page);
                //$page = preg_replace('/<th class="text-right" title="Game Number">#<\/th>/', '<th class="text-right" title="Game Number"></th>', $page);
                $page = preg_replace('/<td class="text-right">\\s+\\d+\\s+<\/td>/', '<td class="text-right"></td>', $page);
                $page = preg_replace('/<th class="span2"><\/th>/', '<th class="span2"> Boxscore </th>', $page);
                
                $page = preg_replace('/<th class="text-right" title="Game Number">#<\/th>/', '<th class="text-right" title="Game Number"></th>', $page);
                
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
             * @param bool $mock If you're passing in mock html instead of a url for testing
             * @return string[] An array of all data one layer deep in html tags 
             */
            public static function parseHTML(string $url, string $startTag = '', string $endTag = '', bool $mock = false): array {
                if(!$mock)
                    $page = TableGrabber::grabPage ($url, $startTag, $endTag);
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
             * @param bool $mock If you're passing in mock html instead of a url for testing
             * @return string[][] A 2d array of all data one layer deep in html tags 
             */
            public static function parseTable(string $url, string $startTag, string $endTag, int $rowWidth, bool $mock = false): array {
                $data = TableGrabber::parseHTML($url, $startTag, $endTag, $mock);

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
        
        // Examples
        //
        // Grab a table
        //$table1 = TableGrabber::parseTable("http://d15k3om16n459i.cloudfront.net/prostats/teamplayerstats.html?teamid=514106&seasonid=16169%27", '<table width="98%" class="tablelines" cellpadding="2" border="0" cellspacing="1">', '</table>', 17);
        //$table1 = TableGrabber::parseTable("http://achahockey.org/stats/statistics/team/514106?leagueid=1800&conferenceid=1151&divisionid=77500&seasonid=16169/stats/statistics/team/514106?leagueid=1800&teamid=514106&site_id=2439&page=statistics&web_page_id=103177&web_page_title=Stats&full_calendar="
        //        , '<table class="table table-striped table-bordered table-hover table-condensed table-stats table-sort">', '/table>', 10);
        
        //TableGrabber::saveTable('datatables\\table1.csv', $table1);
        
        $table2 = TableGrabber::parseTable("achahockey.org/stats/schedule/team/514106?leagueid=1800&conferenceid=1151&divisionid=77500&seasonid=16169&teamid=514106&site_id=2439&page=schedule&web_page_id=103177&web_page_title=Stats&full_calendar="
                , '<table class="table table-striped table-bordered table-hover table-condensed table-stats">', '/table>', 9);
        
                //$table2 = TableGrabber::parseTable("http://d15k3om16n459i.cloudfront.net/prostats/teamroster.html?teamid=514106&seasonid=16169", '<table width="98%" class="tablelines" cellpadding="2" border="0" cellspacing="1">', "</table>", 6);

        // Create a mock test table that I can easily edit
        //$mockHTML = '<td >6</td><td class="light" align="left"><a href="playerpage.html?playerid=9706125&seasonid=16169">Padden, Ryan</a></td><td >F</td><td >26</td><td >29</td><td >25</td><td class="sortcell">54</td><td >    0</td><td >15</td><td >5</td><td >6</td><td >1</td><td >1</td><td >1</td><td >2.08</td><td >0</td><td >  .000</td></tr><tr align="center" class="light"><td >20</td><td class="light" align="left"><a href="playerpage.html?playerid=9706140&seasonid=16169">Forbes, Jay</a></td><td >F</td><td >24</td><td >16</td><td >27</td><td class="sortcell">43</td><td >    0</td><td >50</td><td >7</td><td >2</td><td >0</td><td >0</td><td >3</td><td >1.79</td><td >0</td><td >  .000</td></tr><tr align="center" class="maincolor"><td >93</td><td class="light" align="left"><a href="playerpage.html?playerid=9706139&seasonid=16169">croft, bridger</a></td><td >F</td><td >26</td><td >25</td><td >16</td><td class="sortcell">41</td><td >    0</td><td >30</td><td >4</td><td >3</td><td >2</td><td >0</td><td >2</td><td >1.58</td><td >0</td><td >  .000</td></tr><tr align="center" class="light"><td >11</td><td class="light" align="left"><a href="playerpage.html?playerid=9706124&seasonid=16169">Bing, Luke</a></td><td >CE</td><td >26</td><td >18</td><td >22</td><td class="sortcell">40</td><td >    0</td><td >4</td><td >3</td><td >5</td><td >2</td><td >1</td><td >2</td><td >1.54</td><td >0</td><td >  .000</td></tr><tr align="center" class="maincolor"><td >12</td><td class="light" align="left"><a href="playerpage.html?playerid=9703156&seasonid=16169">Mesec, Connor</a></td><td >D</td><td >23</td><td >3</td><td >11</td><td class="sortcell">14</td><td >    0</td><td >6</td><td >0</td><td >5</td><td >0</td><td >1</td><td >0</td><td >0.61</td><td >0</td><td >  .000</td></tr><tr align="center" class="light"><td >19</td><td class="light" align="left"><a href="playerpage.html?playerid=9705588&seasonid=16169">Platt, George</a></td><td >F</td><td >25</td><td >2</td><td >12</td><td class="sortcell">14</td><td >    0</td><td >10</td><td >0</td><td >1</td><td >0</td><td >1</td><td >0</td><td >0.56</td><td >0</td><td >  .000</td></tr><tr align="center" class="maincolor"><td >30</td><td class="light" align="left"><a href="playerpage.html?playerid=9706156&seasonid=16169">Matheson, Lance</a></td><td >F</td><td >22</td><td >4</td><td >8</td><td class="sortcell">12</td><td >    0</td><td >22</td><td >0</td><td >0</td><td >1</td><td >1</td><td >1</td><td >0.55</td><td >0</td><td >  .000</td></tr><tr align="center" class="light"><td >4</td><td class="light" align="left"><a href="playerpage.html?playerid=9887353&seasonid=16169">Endres, Brandon</a></td><td >L</td><td >11</td><td >4</td><td >8</td><td class="sortcell">12</td><td >    0</td><td >33</td><td >0</td><td >0</td><td >1</td><td >0</td><td >0</td><td >1.09</td><td >0</td><td >  .000</td></tr><tr align="center" class="maincolor"><td >18</td><td class="light" align="left"><a href="playerpage.html?playerid=9706122&seasonid=16169">Dahl, Connor</a></td><td >D</td><td >24</td><td >4</td><td >5</td><td class="sortcell">9</td><td >    0</td><td >44</td><td >0</td><td >0</td><td >0</td><td >0</td><td >1</td><td >0.38</td><td >0</td><td >  .000</td></tr><tr align="center" class="light"><td >5</td><td class="light" align="left"><a href="playerpage.html?playerid=8350661&seasonid=16169">Sherman, Aaron</a></td><td >D</td><td >23</td><td >1</td><td >7</td><td class="sortcell">8</td><td >    0</td><td >24</td><td >0</td><td >1</td><td >0</td><td >0</td><td >0</td><td >0.35</td><td >0</td><td >  .000</td></tr><tr align="center" class="maincolor"><td >17</td><td class="light" align="left"><a href="playerpage.html?playerid=9073346&seasonid=16169">Okon, Taylor</a></td><td >D</td><td >18</td><td >4</td><td >3</td><td class="sortcell">7</td><td >    0</td><td >4</td><td >1</td><td >0</td><td >0</td><td >0</td><td >0</td><td >0.39</td><td >0</td><td >  .000</td></tr><tr align="center" class="light"><td >10</td><td class="light" align="left"><a href="playerpage.html?playerid=8368825&seasonid=16169">Petereit, Mark</a></td><td >F</td><td >3</td><td >3</td><td >4</td><td class="sortcell">7</td><td >    0</td><td >0</td><td >0</td><td >1</td><td >0</td><td >0</td><td >0</td><td >2.33</td><td >0</td><td >  .000</td></tr><tr align="center" class="maincolor"><td >23</td><td class="light" align="left"><a href="playerpage.html?playerid=9706160&seasonid=16169">Johnson, Dylan</a></td><td >D</td><td >25</td><td >4</td><td >2</td><td class="sortcell">6</td><td >    0</td><td >6</td><td >0</td><td >0</td><td >0</td><td >0</td><td >2</td><td >0.24</td><td >0</td><td >  .000</td></tr><tr align="center" class="light"><td >14</td><td class="light" align="left"><a href="playerpage.html?playerid=9731509&seasonid=16169">Stellmack, Nicholas</a></td><td >D</td><td >26</td><td >3</td><td >3</td><td class="sortcell">6</td><td >    0</td><td >24</td><td >0</td><td >0</td><td >0</td><td >0</td><td >0</td><td >0.23</td><td >0</td><td >  .000</td></tr><tr align="center" class="maincolor"><td >8</td><td class="light" align="left"><a href="playerpage.html?playerid=9706142&seasonid=16169">Engh, Brian</a></td><td >F</td><td >8</td><td >4</td><td >1</td><td class="sortcell">5</td><td >    0</td><td >6</td><td >0</td><td >0</td><td >0</td><td >0</td><td >0</td><td >0.63</td><td >0</td><td >  .000</td></tr><tr align="center" class="light"><td >13</td><td class="light" align="left"><a href="playerpage.html?playerid=9706126&seasonid=16169">Dringman, Bobby</a></td><td >D</td><td >22</td><td >1</td><td >4</td><td class="sortcell">5</td><td >    0</td><td >6</td><td >0</td><td >0</td><td >0</td><td >0</td><td >0</td><td >0.23</td><td >0</td><td >  .000</td></tr><tr align="center" class="maincolor"><td >16</td><td class="light" align="left"><a href="playerpage.html?playerid=9706158&seasonid=16169">Beisher, Brice</a></td><td >F</td><td >23</td><td >2</td><td >2</td><td class="sortcell">4</td><td >    0</td><td >53</td><td >0</td><td >0</td><td >0</td><td >0</td><td >1</td><td >0.17</td><td >0</td><td >  .000</td></tr><tr align="center" class="light"><td >22</td><td class="light" align="left"><a href="playerpage.html?playerid=9885211&seasonid=16169">Ratliff, Gavin</a></td><td >Ce</td><td >9</td><td >2</td><td >2</td><td class="sortcell">4</td><td >    0</td><td >2</td><td >0</td><td >0</td><td >0</td><td >0</td><td >0</td><td >0.44</td><td >0</td><td >  .000</td></tr><tr align="center" class="maincolor"><td >21</td><td class="light" align="left"><a href="playerpage.html?playerid=9706157&seasonid=16169">Weidner, Colt</a></td><td >D</td><td >15</td><td >0</td><td >3</td><td class="sortcell">3</td><td >    0</td><td >4</td><td >0</td><td >0</td><td >0</td><td >0</td><td >0</td><td >0.20</td><td >0</td><td >  .000</td></tr><tr align="center" class="light"><td >7</td><td class="light" align="left"><a href="playerpage.html?playerid=8198246&seasonid=16169">Schaeffer, Colton</a></td><td >D</td><td >25</td><td >1</td><td >0</td><td class="sortcell">1</td><td >    0</td><td >6</td><td >0</td><td >0</td><td >0</td><td >0</td><td >0</td><td >0.04</td><td >0</td><td >  .000</td></tr><tr align="center" class="maincolor"><td >9</td><td class="light" align="left"><a href="playerpage.html?playerid=9705570&seasonid=16169">Pertzborn, Henry</a></td><td >D</td><td >7</td><td >0</td><td >1</td><td class="sortcell">1</td><td >    0</td><td >4</td><td >0</td><td >0</td><td >0</td><td >0</td><td >0</td><td >0.14</td><td >0</td><td >  .000</td></tr><tr align="center" class="light"><td >3</td><td class="light" align="left"><a href="playerpage.html?playerid=9731141&seasonid=16169">Griffith, Ryan</a></td><td >D</td><td >12</td><td >0</td><td >0</td><td class="sortcell">0</td><td >    0</td><td >0</td><td >0</td><td >0</td><td >0</td><td >0</td><td >0</td><td >0.00</td><td >0</td><td >  .000</td></tr>';

        // Grab the mock table
        //$mockTable = TableGrabber::parseTable($mockHTML, '<table width="98%" class="tablelines" cellpadding="2" border="0" cellspacing="1">', '</table>', 17, true);

        // Render all the tables
        //TableGrabber::renderTable($table1);
        TableGrabber::renderTable($table2);
        //TableGrabber::renderTable($mockTable);

        

        // Load & render $table1
        //$table3 = TableGrabber::loadTable('table1.csv');
        //TableGrabber::renderTable($table3);
        ?>
    </body>
</html>
