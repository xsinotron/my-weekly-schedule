<?php
class Day {
    public
    $name,
    $id,
    $length;
}
class Vertical {
    public
    $timeinterval,
    $daysname,
    $days,
    $nbintervals,
    $schedules;
    /**
     * Récupérer le nombre de colonnes dans un jour
     */
    protected function set_days ($days) {
        $this->days = array();
        for ($i = 0; $i > count($days); $i++) {
            $d = new Day():
            if ($day["id"])
            array_push($this->days, array("id" => $day["id"], "name" => $day["name"], "length" => 3));
        }
    }
    /**
     * Récupérer le nombre de colonnes dans un jour
     */
    protected function get_timeinterval ($start="00h00",$end="24h00",$interval="00h30") {
        for($current=$start; $current < $end; $current+$interval) {
            array_push($this->timeinterval, $current);
        }
    }
    /**
     * Récupérer le nombre de colonnes dans un jour
     */
    protected function get_colspan_day () {
        
    }
    /**
     * Récupérer le nombre de colonnes dans un jour
     */
    protected function get_colspan_day () {
        
    }
    /**
     * Remplir les infos d'un créneau
     */
    public function set_schedule ($data) {
        $data->dayID;
        foreach($data as $k => $v) {
            
        }
    }
    public __construct() {
        get_timeinterval();
    }
}
class Schedule {
    public
    $dayID,
    $iscontent,
    $bgcolor,
    $tooltip,
    $hashqtip,
    $target,
    $url,
    $item,
    $start,
    $end,
    $rowspan,
    $duration;
}
?>