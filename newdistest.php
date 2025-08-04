<?php
require('globals.php');
?>
<style>
.tile {
    border: 1px solid #ddd;
    min-height: 100px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.tile .troops {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: rgba(0, 0, 0, 0.6);
    color: white;
    padding: 5px;
    border-radius: 50%;
}

.tile:hover {
    background-color: #f4f4f4;
}
</style>

<div class="container mt-4">
<h2>Risk Game Grid</h2>
<div class="row">
<!-- Repeat this for each row of tiles -->
<div class="col-2 p-2">
<div class="tile">
<div class="troops">3</div>
</div>
</div>
<div class="col-2 p-2">
<div class="tile">
<div class="troops">5</div>
</div>
</div>
<div class="col-2 p-2">
<div class="tile">
<div class="troops">2</div>
</div>
</div>
<div class="col-2 p-2">
<div class="tile">
<div class="troops">1</div>
</div>
</div>
<div class="col-2 p-2">
<div class="tile">
<div class="troops">1,4</div>
</div>
</div>
<div class="col-2 p-2">
<div class="tile">
<div class="troops">6</div>
</div>
</div>
</div>

<div class="row mt-2">
<!-- Another row of tiles, can be repeated for the game grid -->
<div class="col-2 p-2">
<div class="tile">
<div class="troops">3</div>
</div>
</div>
<div class="col-2 p-2">
<div class="tile">
<div class="troops">5</div>
</div>
</div>
<div class="col-2 p-2">
<div class="tile">
<div class="troops">2</div>
</div>
</div>
<div class="col-2 p-2">
<div class="tile">
<div class="troops">1</div>
</div>
</div>
<div class="col-2 p-2">
<div class="tile">
<div class="troops">4</div>
</div>
</div>
<div class="col-2 p-2">
<div class="tile">
<div class="troops">6</div>
</div>
</div>
</div>
</div>
<?php 
$h->endpage();
?>
