<!DOCTYPE html>
<html>

<?php include($this->rootPath("webapp/boutique/elements/templates/head.php")); ?>


<body class="fixed-sidebar">

    <div id="wrapper">

        <?php include($this->rootPath("webapp/boutique/elements/templates/sidebar.php")); ?>  

        <div id="page-wrapper" class="gray-bg">

          <?php include($this->rootPath("webapp/boutique/elements/templates/header.php")); ?>  

          <div class="wrapper wrapper-content">
            <div class="animated fadeInRightBig">

                <div class=" border-bottom white-bg dashboard-header">
                    <br>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <img src="<?= $this->stockage("images", "societe", $params->image) ?>" style="height: 70px;" alt=""><br>
                                <h2 class="text-uppercase"><?= $boutique->name() ?></h2><br>
                            </div>
                            <small><?= $boutique->lieu  ?> </small>
                            <ul class="list-group clear-list m-t">
                                <li class="list-group-item fist-item">
                                    Commandes en cours <span class="label label-success float-right"><?= start0(count($groupes__)); ?></span> 
                                </li>
                                <li class="list-group-item">
                                    Livraisons en cours <span class="label label-success float-right"><?= start0(count($livraisons__)); ?></span> 
                                </li>
                                <li class="list-group-item">
                                    Prospections en cours <span class="label label-success float-right"><?= start0(count($prospections__)); ?></span> 
                                </li>
                                <li class="list-group-item"></li>
                            </ul>
                        </div>
                        <div class="col-md-8">
                            <div class="text-center">
                                <div class="flot-chart">
                                    <div class="flot-chart-content" id="flot-dashboard-chart"></div>
                                </div><hr>
                                <small>Graphe de comparaison des différents modes de ventes</small>
                            </div><hr>
                            <div class="row text-center">
                                <div class="col">
                                    <div class="">
                                        <span class="h5 font-bold block text-primary"><?= money(comptage(Home\VENTE::direct(dateAjoute(), dateAjoute(), $boutique->id), "montant", "somme")); ?> <small><?= $params->devise ?></small></span>
                                        <small class="text-muted block">Ventes directes</small>
                                    </div>
                                </div>
                                <div class="col border-right border-left text-danger">
                                    <span class="h5 font-bold block"><?= money(comptage(Home\VENTE::prospection(dateAjoute(), dateAjoute(), $boutique->id), "montant", "somme")); ?> <small><?= $params->devise ?></small></span>
                                    <small class="text-muted block">Ventes par prospection</small>
                                </div>
                                <div class="col text-blue">
                                    <span class="h5 font-bold block"><?= money(comptage(Home\VENTE::cave(dateAjoute(), dateAjoute(), $boutique->id), "montant", "somme")); ?> <small><?= $params->devise ?></small></span>
                                    <small class="text-muted block">Ventes en cave</small>
                                </div>
                                <div class="col border-right border-left text-danger">
                                    <span class="h5 font-bold block"><?= money(comptage(Home\VENTE::commande(dateAjoute(), dateAjoute(), $boutique->id), "montant", "somme")); ?> <small><?= $params->devise ?></small></span>
                                    <small class="text-muted block">Commandes/Livraisons</small>
                                </div>
                            </div>
                        </div>
                   <!--      <div class="col-md-3 border-left">
                            <div class="statistic-box" style="margin-top: 0%">
                             <div class="ibox">
                                <div class="ibox-content">
                                    <h5>Courbe des ventes</h5>
                                    <div id="sparkline2"></div>
                                </div>

                                <div class="ibox-content">
                                    <h5>Dette chez les clients</h5>
                                    <h2 class="no-margins"><?= 0 //money(Home\CLIENT::Dettes()); ?> <?= $params->devise  ?></h2>
                                </div>

                                <div class="ibox-content">
                                    <h5>En rupture de Stock</h5>
                                    <h2 class="no-margins"><?= 0 //start0(count(Home\PRODUIT::ruptureBoutique($boutique->id))) ?> produit(s)</h2>
                                </div>
                            </div>
                        </div>
                    </div> -->
                </div><hr>

                <div class="text-center">
                    <button data-toggle=modal data-target="#modal-vente" class="btn btn-warning dim"> <i class="fa fa-long-arrow-right"></i> Nouvelle vente directe</button>
                    <button data-toggle="modal" data-target="#modal-prospection" class="btn btn-primary dim"><i class="fa fa-bicycle"></i> Nouvelle prospection</button> 
<!--                     <button data-toggle="modal" data-target="#modal-ventecave" class="btn btn-success dim"><i class="fa fa-home"></i> Nouvelle vente en cave</button>
 -->                </div>
            </div>


        </div>
    </div>
    <br>

    <?php include($this->rootPath("webapp/boutique/elements/templates/footer.php")); ?>

    <?php include($this->rootPath("composants/assets/modals/modal-clients.php")); ?> 
    <?php include($this->rootPath("composants/assets/modals/modal-client.php")); ?> 
    <?php include($this->rootPath("composants/assets/modals/modal-vente.php")); ?> 
    <?php include($this->rootPath("composants/assets/modals/modal-prospection.php")); ?> 
    <?php include($this->rootPath("composants/assets/modals/modal-ventecave.php")); ?> 

</div>
</div>


<?php include($this->rootPath("webapp/boutique/elements/templates/script.php")); ?>

<script type="text/javascript" src="<?= $this->relativePath("../../master/client/script.js") ?>"></script>
<script type="text/javascript" src="<?= $this->relativePath("../../production/miseenboutique/script.js") ?>"></script>

<script>
    $(document).ready(function() {

    var id = "<?= $this->id;  ?>";
    if (id == 1) {
        setTimeout(function() {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 4000
            };
            toastr.success('Content de vous revoir de nouveau!', 'Bonjour <?= $employe->name(); ?>');
        }, 1300);
}



var sparklineCharts = function(){

 $("#sparkline2").sparkline([24, 43, 43, 55, 44, 62, 44, 72], {
     type: 'line',
     width: '100%',
     height: '60',
     lineColor: '#1ab394',
     fillColor: "#ffffff"
 });

};

var sparkResize;

$(window).resize(function(e) {
    clearTimeout(sparkResize);
    sparkResize = setTimeout(sparklineCharts, 500);
});

sparklineCharts();




var data1 = [<?php foreach ($stats as $key => $lot) { ?>[gd(<?= $lot->year ?>, <?= $lot->month ?>, <?= $lot->day ?>), <?= $lot->direct ?>], <?php } ?> ];

 var data2 = [<?php foreach ($stats as $key => $lot) { ?>[gd(<?= $lot->year ?>, <?= $lot->month ?>, <?= $lot->day ?>), <?= $lot->prospection ?>], <?php } ?> ];

     var data3 = [<?php foreach ($stats as $key => $lot) { ?>[gd(<?= $lot->year ?>, <?= $lot->month ?>, <?= $lot->day ?>), <?= $lot->cave ?>], <?php } ?> ];

         var dataset = [
         {
            label: "Vente directe",
            data: data1,
            color: "#1ab394",
            bars: {
                show: true,
                align: "left",
                barWidth: 12 * 60 * 60 * 600,
                lineWidth:0
            }

        }, {
            label: "Vente par prospection",
            data: data2,
            color: "#cc0000",
            bars: {
                show: true,
                align: "right",
                barWidth: 12 * 60 * 60 * 600,
                lineWidth:0
            }

        }, {
            label: "Vente en cave",
            data: data3,
            color: "#0044cc",
            bars: {
                show: true,
                align: "right",
                barWidth: 12 * 60 * 60 * 600,
                lineWidth:0
            }

        }
        ];


        var options = {
            xaxis: {
                mode: "time",
                tickSize: [2, "day"],
                tickLength: 0,
                axisLabel: "Date",
                axisLabelUseCanvas: true,
                axisLabelFontSizePixels: 12,
                axisLabelFontFamily: 'Arial',
                axisLabelPadding: 10,
                color: "#d5d5d5"
            },
            yaxes: [{
                position: "left",
                color: "#d5d5d5",
                axisLabelUseCanvas: true,
                axisLabelFontSizePixels: 12,
                axisLabelFontFamily: 'Arial',
                axisLabelPadding: 3
            }
            ],
            legend: {
                noColumns: 1,
                labelBoxBorderColor: "#000000",
                position: "nw"
            },
            grid: {
                hoverable: false,
                borderWidth: 0
            }
        };

        function gd(year, month, day) {
            return new Date(year, month - 1, day).getTime();
        }

        var previousPoint = null, previousLabel = null;

        $.plot($("#flot-dashboard-chart"), dataset, options);



    });
</script>


</body>

</html>