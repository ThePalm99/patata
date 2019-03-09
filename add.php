<!DOCTYPE html>
<?php
include 'db.php';
include 'functions.php';
?>
<html>
    <head>
        <link rel="icon" type="image/svg+xml" href="patata.svg">
        <script src="https://code.jquery.com/jquery-3.3.1.min.js"  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
        <link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" />
        <script type="text/javascript" src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    </head>
    <body onload="display_ct()">
        <div class="container">
            <h1>Patata</h1>

            <div class="row">
                <div class="col-md-6">      
                    <div id='ct'></div>
                    <div id='ct2'></div>
                </div>
                <div class="col-md-6">
                    <div align=right id='quotesbox'></div>
                    <div align=right id='authorbox'></div>
                </div>
            </div>

            <hr/>
	        <?php handle_post(); ?>
            <div id='tasktable'>
                <div class="task">
                    <h5 class="text-center">Tasklist</h5>
                    <table class="table table-striped " style="width: 70%; margin: 0 auto;">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Durate (Minutes)</th>
                                <th>Maintainer</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $db = new MyDB();
                            list($result, $maintainer) = get_tasks_and_maintainers($db);

                            $emoText = array("C"=>"🍀", "E"=>"⚡", "I"=>"💻", "S"=>"🎮");
                            $emoDescription = array("C"=>"Cose", "E"=>"Elettronica", "I"=>"Informatica", "S"=>"Svago");

                            while ($tasklist = $result->fetchArray(SQLITE3_ASSOC)):

                                ?><form method="post" action="add.php">
                                <tr>
                                <input type="hidden" name="idn" value="<?= $tasklist['ID'] ?>">

                                <td>
                                    <select required name="tasktype">
                                        <?php foreach($emoText as $text => $emoji){?>
                                            <option value="<?= $text ?>"
                                            <?= $text === $tasklist['TaskType'] ? " selected" : "" ?>
                                            title="<?= $emoDescription[$text] ?>" ><?= "$emoji $emoDescription[$text]" ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td><input type="text" name="title" value="<?= $tasklist['Title'] ?>"></td>
                                <td><input type="text" name="description" value="<?= isset($tasklist['Description']) ? $tasklist['Description']: "" ?>"></td>
                                <td><input type="text" name="durate" size="3" value="<?= $tasklist['Durate'] ?>"></td>
                                <td><input type="text" name="maintainer" value="<?= isset($maintainer[$tasklist['ID']]) ? implode(', ',$maintainer[$tasklist['ID']]) : "" ?>"></td>
                                <td><input type="submit" name="submit" value="Save"></td>
                                </tr>
                                </form>
                            <?php endwhile; ?>
                            <form method="post" action="add.php">
                                <tr>
                                <td><select required name="tasktype">
                                    <option></option>
                                    <?php foreach($emoText as $text => $emoji){
                                        ?><option value="<?= $text ?>"><?= "$emoji $emoDescription[$text]" ?></option>
                                    <?php } ?>

                                </select></td>
                                <td><input type="text" name="title"></td>
                                <td><input type="text" name="description"></td>
                                <td><input type="text" name="durate" size="3"></td>
                                <td><input type="text" name="maintainer"></td>
                                <td><input type="submit" name="submit" value="Add"></td>
                                </tr>
                                </form>
                        </tbody>                 
                    </table> 
                </div>  
            </div>
            <hr/>                          

            <div class="row">
                <div class="col-md-6">
                    <div class="statswrapper">
                        <h6 class="text-center">Recently added items</h6>
                        <table class="table table-striped table-sm text-center">
                            <thead>
                                <tr>
                                    <td>Item</td>
                                    <td>Added</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><a href="/item/R374">R374</a></td>
                                    <td>2018-10-18, 20:01</td>
                                </tr>
                                <tr>
                                    <td><a href="/item/R373">R373</a></td>
                                    <td>2018-10-18, 20:01</td>
                                </tr>
                                <tr>
                                    <td><a href="/item/C165">C165</a></td>
                                    <td>2018-10-18, 20:01</td>
                                </tr>
                                <tr>
                                    <td><a href="/item/B152">B152</a></td>
                                    <td>2018-10-18, 20:01</td>
                                </tr>
                                <tr>
                                    <td><a href="/item/R372">R372</a></td>
                                    <td>2018-10-18, 13:20</td>
                                </tr>
                                <tr>
                                    <td><a href="/item/R371">R371</a></td>
                                    <td>2018-10-18, 13:20</td>
                                </tr>
                                <tr>
                                    <td><a href="/item/127">127</a></td>
                                    <td>2018-10-16, 19:37</td>
                                </tr>
                                <tr>
                                    <td><a href="/item/126">126</a></td>
                                    <td>2018-10-16, 19:16</td>
                                </tr>
                                <tr>
                                    <td><a href="/item/HDD228">HDD228</a></td>
                                    <td>2018-10-16, 19:07</td>
                                </tr>
                                <tr>
                                    <td><a href="/item/HDD227">HDD227</a></td>
                                    <td>2018-10-16, 19:04</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="statswrapper">
                        <h6 class="text-center">Recently modified items</h6>
                        <table class="table table-striped table-sm text-center">
                            <thead>
                                <tr>
                                    <td>Item</td>
                                    <td>Modified</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><a href="/item/R374">R374</a></td>
                                    <td>2018-10-18, 20:01</td>
                                </tr>
                                <tr>
                                    <td><a href="/item/R373">R373</a></td>
                                    <td>2018-10-18, 20:01</td>
                                </tr>
                                <tr>
                                    <td><a href="/item/C165">C165</a></td>
                                    <td>2018-10-18, 20:01</td>
                                </tr>
                                <tr>
                                    <td><a href="/item/B152">B152</a></td>
                                    <td>2018-10-18, 20:01</td>
                                </tr>
                                <tr>
                                    <td><a href="/item/R372">R372</a></td>
                                    <td>2018-10-18, 13:20</td>
                                </tr>
                                <tr>
                                    <td><a href="/item/R371">R371</a></td>
                                    <td>2018-10-18, 13:20</td>
                                </tr>
                                <tr>
                                    <td><a href="/item/127">127</a></td>
                                    <td>2018-10-16, 19:37</td>
                                </tr>
                                <tr>
                                    <td><a href="/item/126">126</a></td>
                                    <td>2018-10-16, 19:16</td>
                                </tr>
                                <tr>
                                    <td><a href="/item/HDD228">HDD228</a></td>
                                    <td>2018-10-16, 19:07</td>
                                </tr>
                                <tr>
                                    <td><a href="/item/HDD227">HDD227</a></td>
                                    <td>2018-10-16, 19:04</td>
                                </tr>
                            </tbody>
                        </table>
                    <div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            function display_c(){       //Refresh time for the date function
                var refresh=1000;
                mytime=setTimeout('display_ct()',refresh)
            }

            function addZero(i) {       
                if (i < 10) {
                    i = "0" + i;
                }
                return i;
            }

            function display_ct() {     //Date generator function
                var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                var x = new Date()
                var d = addZero(x.getDate())
                var mo = months[x.getMonth()]
                var y = addZero(x.getFullYear())
                var h = addZero(x.getHours())
                var mi = addZero(x.getMinutes())
                var s = addZero(x.getSeconds())
                var wd = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"]
                var td = wd[x.getDay()]
                var x1 = td + " - " + d + " " + mo + " " + y
                var x2 = h + ":" + mi + ":" + s
                document.getElementById('ct').innerHTML = x1
                document.getElementById('ct2').innerHTML = x2

                display_c();
            }

            <?php
                $quotesin = file_get_contents("quotes.json");
                $obj2 = json_decode("$quotesin", TRUE);
                $num_quotes = count($obj2) - 1;
                $quote_refresh = 5; // In seconds
            
            //display_q() Refresh time function for the quotes
            //display_qt() Quote string generator
                echo "
                function display_q(){                                      
                    var refresh=1000*$quote_refresh;
                    mytime=setTimeout('display_qt()',refresh)
                }

                function display_qt() {

                    var quotes_array = $quotesin
                    var val = Math.floor(Math.random() * $num_quotes)
                    document.getElementById('quotesbox').innerHTML = quotes_array[val].quote
                    document.getElementById('authorbox').innerHTML = \"Cit. \" + quotes_array[val].author
                    if(typeof quotes_array[val].context !== 'undefined'){
                        document.getElementById('authorbox').innerHTML = \"Cit. \" + quotes_array[val].author + \" \" + quotes_array[val].context
                    }
                    else{
                    document.getElementById('authorbox').innerHTML = \"Cit. \" + quotes_array[val].author
                    }
                    display_q();
                }";
            ?>
        </script>
    </body>
</html>