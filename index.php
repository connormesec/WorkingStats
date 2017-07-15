<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        //Done once a week
        
        $statsFile = fopen("stats.cvs", "r");
        
        $oldTime = fgets($statsFile);
        
        $expire_date = strtotime(' + 7 days', strtotime($oldTime));
        
        fclose($statsFile);
        
        if (true){
            //Grab the table html
            $curl = curl_init('http://d15k3om16n459i.cloudfront.net/prostats/teamplayerstats.html?teamid=514106&seasonid=16169');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

            $page = curl_exec($curl);

            if (curl_errno($curl)) {
                echo ('Scraper error: ') . curl_error($curl);
                exit;
            }

            curl_close($curl);

            $regex = '/<table width="98%" class="tablelines" cellpadding="2" border="0" cellspacing="1">(.*?)<\/table>/s';
            if (preg_match($regex, $page, $list)) {
                
            }else{
                echo('Error');
            }

            //Parse the data into csv
            $lines = explode("\n", $list[0]);

            $stats = array(array());

            for($i = 18; $i < count($lines)-34; $i+=18) {
                for($j = 2; $j <= 17; $j++) {
                    if(strpos($lines[$i+$j], "class=\"light\"") !== false) {
                        $stats[$i][$j] = explode("<", explode(">", $lines[$i+$j])[2])[0];
                    } else {
                        $stats[$i][$j] = explode("<", explode(">", $lines[$i+$j])[1])[0];
                    }
                }
            }

            $statsFile = fopen("stats.cvs", "w+");

            fwrite($statsFile, date("d m Y"));

            foreach($stats as $column) {
                foreach($column as $data) 
                    fwrite($statsFile, trim($data) . "#");
                fwrite($statsFile, "\n");
            }
        }
        
        //ran every time
        
        $statsFile = fopen("stats.cvs", "r");
        
        fgets($statsFile);
        
        echo('<table width="98%" class="tablelines" cellpadding="2" border="0" cellspacing="1">');
        
        while(!feof($statsFile)) {
            $line = fgets($statsFile);
            
            if(trim($line) == '') continue;
            
            $statVars = explode("#", $line);
            echo('<tr align="center" class="light">');
            for($j = 0; $j < 16; $j++)
                echo("<td>" . $statVars[$j] . "</td>");
            echo("</tr>");
        }
        echo("</table>");
        
        fclose($statsFile);
        ?>
    </body>
</html>
