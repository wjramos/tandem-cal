<!-- This file is used to markup the public-facing widget. -->
<?php //echo $xml_string; ?>
<h2 class="events-title">Events</h2>
<ul class="events-feed">
    <?php
    foreach ($events as $event){
        ?>
        <li class="event">
           <?php
    //var_dump($event);
           $start = $event['time_start'];
           list($date_start, $time_start) = explode(" ", "$start ");

        //Reformat dates and times, leave out year and seconds
           $date_start = date("M-d", strtotime($date_start));
           $time_start = date("g:ia", strtotime($time_start));
           list($month_start, $day_start) = explode("-", "$date_start ");
           $end = $event['date_end'];
           list($date_end, $time_end) = explode(" ", "$end ");
           $date_end = date("M-d", strtotime($date_end));
           $time_end = date("g:ia", strtotime($time_end));
           ?>
           <div class="event-dt">
            <p class="mnth"><?php echo $month_start; ?></p>
            <p class="day"><?php echo $day_start; ?></p>
        </div>
        <div class="event-dsc">

           <h3 class="ttl"><a href ="<?php echo $event['link'] ?>" target="_blank"><?php echo $event["name"]; ?></a></h3>
           <p class="time"><?php echo $time_start . '&nbsp;&nbsp; - &nbsp;' . $time_end; ?></p>
       </div>
   </li>
   <?php } ?>
</ul>