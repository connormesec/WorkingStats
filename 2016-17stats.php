<?php
    //Example of code that will go into a page on the website
    //require_once('index.php');
    $stats16 = loadTable('stats16.csv');
    renderTable($stats16);
    $stats16g = loadTable('stats16g.csv');
    renderTable($stats16g);
    function renderTable(array $table):void {
                echo('<table width="98%" class="tablelines" cellpadding="2" border="0" cellspacing="1">');

                foreach($table as $row) {
                    echo('<tr align="center" class="light">');
                    foreach($row as $cell)
                        echo ('<td>' . $cell . '</td>');
                    echo('</tr>');
                }

                echo('</table>');
            }
    function loadTable(string $fileName, string $delimiter = '%'): array {
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
            
?>    