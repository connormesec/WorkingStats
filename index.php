<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        include('TableGrabber.php');
        
        // TODO: Clean all this up
        
        // Examples

        //
        // Grab a table
        //$table1 = TableGrabber::parseTable("http://d15k3om16n459i.cloudfront.net/prostats/teamplayerstats.html?teamid=514106&seasonid=16169%27", '<table width="98%" class="tablelines" cellpadding="2" border="0" cellspacing="1">', '</table>', 17);
        //$table1 = TableGrabber::parseTable("http://achahockey.org/stats/statistics/team/514106?leagueid=1800&conferenceid=1151&divisionid=77500&seasonid=16169/stats/statistics/team/514106?leagueid=1800&teamid=514106&site_id=2439&page=statistics&web_page_id=103177&web_page_title=Stats&full_calendar="
        //        , '<table class="table table-striped table-bordered table-hover table-condensed table-stats table-sort">', '/table>', 10);
        
        //TableGrabber::saveTable('datatables\\table1.csv', $table1);
        
        //$page = preg_replace('/<td class="text-right">\\s+\\d+\\s+<\/td>/', '<td class="text-right"></td>', $page);
        //$page = preg_replace('/<th class="span2"><\/th>/', '<td>Boxscore</td>', $page);
        //$page = preg_replace('/<td>Boxscore<\\/td>/', '<td></td>', $page);
        //$page = preg_replace('/<th class="text-right" title="Game Number">#<\/th>/', '<th class="text-right" title="Game Number"></th>', $page);
                
        $table2 = TableGrabber::parseTable("achahockey.org/stats/schedule/team/514106?leagueid=1800&conferenceid=1151&divisionid=77500&seasonid=16169&teamid=514106&site_id=2439&page=schedule&web_page_id=103177&web_page_title=Stats&full_calendar=",
                '<table class="table table-striped table-bordered table-hover table-condensed table-stats">', '/table>', 9,
                array('/<td class="text-right">\\s+\\d+\\s+<\/td>/:<td class="text-right"></td>',
                    '/<th class="span2"><\/th>/:<th class="span2"> Boxscore </th>',
                    '/<th class="text-right" title="Game Number">#<\/th>/:<th class="text-right" title="Game Number"></th>'));
        
                //$table2 = TableGrabber::parseTable("http://d15k3om16n459i.cloudfront.net/prostats/teamroster.html?teamid=514106&seasonid=16169", '<table width="98%" class="tablelines" cellpadding="2" border="0" cellspacing="1">', "</table>", 6);


        // Grab two test tables (both the ones given)
        function grabTables () {
            $table1 = TableGrabber::parseTable("http://d15k3om16n459i.cloudfront.net/prostats/teamplayerstats.html?teamid=514106&seasonid=17008", '<table width="98%" class="tablelines" cellpadding="2" border="0" cellspacing="1">', '<td colspan=19 class="maincolor">&nbsp;</td>', 17);
            $table1g = TableGrabber::parseTable("http://d15k3om16n459i.cloudfront.net/prostats/teamplayerstats.html?teamid=514106&seasonid=17008", '<a name="goalies"></a>', '<td colspan="13">&nbsp;</td>', 13);
            $table2 = TableGrabber::parseTable("http://d15k3om16n459i.cloudfront.net/prostats/teamroster.html?teamid=514106&seasonid=17008", '<table width="98%" class="tablelines" cellpadding="2" border="0" cellspacing="1">', "</table>", 6);
            $table3 = TableGrabber::parseTable("http://achahockey.org/stats/overview/team/514106?leagueid=1800&conferenceid=1151&divisionid=77500&site_id=2439&page=overview&web_page_id=103177&web_page_title=Stats&full_calendar=", "18 </h3>", "</table>", 10);
            $table4 = TableGrabber::parseTable("http://d15k3om16n459i.cloudfront.net/prostats/teamplayerstats.html?teamid=514106&seasonid=16169", '<table width="98%" class="tablelines" cellpadding="2" border="0" cellspacing="1">', '<td colspan=19 class="maincolor">&nbsp;</td>', 17);
            $table4g = TableGrabber::parseTable("http://d15k3om16n459i.cloudfront.net/prostats/teamplayerstats.html?teamid=514106&seasonid=16169", '<a name="goalies"></a>', '<td colspan="13">&nbsp;</td>', 13);
            $table5 = TableGrabber::parseTable("http://d15k3om16n459i.cloudfront.net/prostats/teamroster.html?teamid=514106&seasonid=16169", '<table width="98%" class="tablelines" cellpadding="2" border="0" cellspacing="1">', "</table>", 6);
            $table6 = TableGrabber::parseTable("http://achahockey.org/stats/overview/team/514106?leagueid=1800&conferenceid=1151&divisionid=77500&site_id=2439&page=overview&web_page_id=103177&web_page_title=Stats&full_calendar=", "17 </h3>", "</table>", 10);
            
            // Save $table1 as table1.csv
            TableGrabber::saveTable('datatables\\stats17.csv', $table1);
            TableGrabber::saveTable('datatables\\stats17g.csv', $table1g);
            TableGrabber::saveTable('datatables\\roster17.csv', $table2);
            TableGrabber::saveTable('datatables\\winloss17.csv', $table3);
            TableGrabber::saveTable('datatables\\stats16.csv', $table4);
            TableGrabber::saveTable('datatables\\stats16g.csv', $table4g);
            TableGrabber::saveTable('datatables\\roster16.csv', $table5);
            TableGrabber::saveTable('datatables\\winloss16.csv', $table6);
            // Load & render $table1

            //Will go in 2017-18 stats page
            $stats17 = TableGrabber::loadTable('datatables\\stats17.csv');
            TableGrabber::renderTable($stats17);
            $stats17g = TableGrabber::loadTable('datatables\\stats17g.csv');
            TableGrabber::renderTable($stats17g);
            //Will go in 2017-18 roster page
            $roster17 = TableGrabber::loadTable('datatables\\roster17.csv');
            TableGrabber::renderTable($roster17);
            //Will go wherever a win/loss table is needed
            $winloss17 = TableGrabber::loadTable('datatables\\winloss17.csv');
            TableGrabber::renderTable($winloss17);
            //Will go in 2016-17 stats page
            $stats16 = TableGrabber::loadTable('datatables\\stats16.csv');
            TableGrabber::renderTable($stats16);
            $stats16g = TableGrabber::loadTable('datatables\\stats16g.csv');
            TableGrabber::renderTable($stats16g);
            //Will go in 2016-17 roster page
            $roster16 = TableGrabber::loadTable('datatables\\roster16.csv');
            TableGrabber::renderTable($roster16);
            //Will go wherever a win/loss table is needed
            $winloss16 = TableGrabber::loadTable('datatables\\winloss16.csv');
            TableGrabber::renderTable($winloss16);
        }
        
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

        //TableGrabber::renderTable($table2);
        //TableGrabber::renderTable($mockTable);

        
        ?>
    </body>
</html>
