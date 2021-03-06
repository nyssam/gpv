<!DOCTYPE html>
<html>

<?php include($this->rootPath("webapp/entrepot/elements/templates/head.php")); ?>


<body class="fixed-sidebar">

    <div id="wrapper">

        <?php include($this->rootPath("webapp/entrepot/elements/templates/sidebar.php")); ?>  

        <div id="page-wrapper" class="gray-bg">

          <?php include($this->rootPath("webapp/entrepot/elements/templates/header.php")); ?>  


          <div class="ibox">
            <div class="ibox-title">
                <h5 class="text-uppercase">Stock d'emballages</h5>
                <div class="ibox-tools">
                    <button data-toggle='modal' data-target="#modal-approemballage" style="margin-top: -2%" class="btn btn-success btn-xs dim"><i class="fa fa-plus"></i> Nouvel Approvisionnement</button>
                    <button style="margin-top: -2%;" type="button" data-toggle=modal data-target='#modal-perteentrepot' class="btn btn-danger btn-xs dim"><i class="fa fa-trash"></i> Enregistrer une perte </button>
                </div>
            </div>
            <div class="ibox-content">
                <div class="row text-center">
                  <?php $total = 0; foreach ($emballages as $key => $emballage) {
                    $stock = $emballage->stock(Home\PARAMS::DATE_DEFAULT, dateAjoute(1), $entrepot->id);
                    $prix = $stock * $emballage->price();
                    $total += $prix ?>
                    <div class="col-sm-4 col-md-3 col-lg-2 border-left border-bottom">
                        <div class="p-xs">
                            <i class="fa fa-flask fa-2x"></i>
                            <h5 class="m-xs gras <?= ($stock > $emballage->stkAlert)?"":"clignote" ?>"><?= round($stock, 2) ?> </h5>
                            <h6 class="no-margins text-uppercase gras <?= ($stock > $emballage->stkAlert)?"":"clignote" ?>"><?= $emballage->name() ?> </h6>
                            <small>Es: <?= money($prix) ?> <?= $params->devise ?></small>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="text-center">
                <h5>Estimation du stock actuel</h5>
                <h1 class="no-margins"><?= money($total) ?> <?= $params->devise ?></h1>
            </div>
        </div>
    </div>



    <div class="wrapper wrapper-content">
        <div class="text-center animated fadeInRightBig">

            <div class="ibox ">
                <div class="ibox-title">
                    <h5 class="float-left text-uppercase">Historiques du <?= datecourt($date1) ?> au <?= datecourt($date2) ?></h5>
                    <div class="ibox-tools">
                        <form id="formFiltrer" method="POST">
                            <div class="row" style="margin-top: -1%">
                                <div class="col-5">
                                    <input type="date" value="<?= $date1 ?>" class="form-control input-sm" name="date1">
                                </div>
                                <div class="col-5">
                                    <input type="date" value="<?= $date2 ?>" class="form-control input-sm" name="date2">
                                </div>
                                <div class="col-2">
                                    <button type="button" onclick="filtrer()" class="btn btn-sm btn-white"><i class="fa fa-search"></i> Filtrer</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="ibox-content">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th rowspan="2" class="border-none"></th>
                                <?php foreach ($emballages as $key => $emballage) {  ?>
                                    <th><small class="gras"><?= $emballage->name() ?></small></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $index = $date1;
                            while ($index <= $date2) { ?>
                                <tr>
                                    <td class="gras"><?= datecourt($index) ?></td>
                                    <?php foreach ($emballages as $key => $emballage) {
                                        $stock = $emballage->stock(Home\PARAMS::DATE_DEFAULT, $index, $entrepot->id);
                                        $appro = $emballage->achat($index, $index, $entrepot->id);
                                        $conso = $emballage->consommee($index, $index, $entrepot->id);
                                        $perte = $emballage->perte($index, $index, $entrepot->id);
                                        ?>
                                        <td class="cursor myPopover"
                                        data-toggle="popover"
                                        data-placement="left"
                                        title="<small><b><?= $emballage->name() ?></b> | <?= datecourt($index) ?></small>"
                                        data-trigger="hover"
                                        data-html="true"
                                        data-content="
                                        <span>Appro du jour : <b><?= round($appro, 2) ?> </b></span><br>
                                        <span>Conso du jour : <b><?= round($conso, 2) ?> </b></span><br>
                                        <span>Perte : <b><?= round($perte, 2) ?> </b></span>
                                        <hr style='margin:1.5%'>
                                        <span>En stock à ce jour : <b><?= round($stock, 2) ?> </b></span><br> <span>">
                                            <?= round($stock, 2) ?> 
                                        </td>
                                    <?php } ?>
                                </tr>
                                <?php
                                $index = dateAjoute1($index, 1);
                            }
                            ?>
                            <tr style="height: 18px;"></tr>
                        </tbody>
                    </table> 
                </div>

            </div>


        </div>
    </div>


    <?php include($this->rootPath("webapp/entrepot/elements/templates/footer.php")); ?>
    <?php include($this->rootPath("composants/assets/modals/modal-approemballage.php")); ?>  

</div>
</div>


<?php include($this->rootPath("webapp/entrepot/elements/templates/script.php")); ?>

<script type="text/javascript" src="<?= $this->relativePath("../approemballage/script.js") ?>"></script>




<div class="modal inmodal fade" id="modal-perteentrepot">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-red">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Enregistrer une perte</h4>
                <small>Veuillez renseigner les informations pour enregistrer la perte</small>
            </div>
            <form method="POST" class="formShamman" classname="perteentrepot">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-8">
                            <label>Emballage perdu <span1>*</span1></label>
                            <div class="form-group">
                                <?php Native\BINDING::html("select", "emballage"); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label>Quantité perdue<span1>*</span1></label>
                            <div class="form-group">
                                <input type="number" number class="form-control" name="quantite" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <label>Cause de la perte<span1>*</span1></label>
                            <div class="form-group">
                                <?php Native\BINDING::html("select", "typeperte"); ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label>Plus de détails<span1>*</span1></label>
                            <div class="form-group">
                                <textarea class="form-control" name="comment" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                </div><hr>
                <div class="container">
                    <input type="hidden" name="id" >
                    <button type="button" class="btn btn-sm  btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Annuler</button>
                    <button class="btn btn-sm btn-danger dim pull-right"><i class="fa fa-money"></i> Enregistrer la perte</button>
                </div>
                <br>
            </form>
        </div>
    </div>
</div>


</body>

</html>
