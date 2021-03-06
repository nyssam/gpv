<!DOCTYPE html>
<html>

<?php include($this->rootPath("webapp/config/elements/templates/head.php")); ?>

<body class="top-navigation">

    <div id="wrapper">
        <div id="page-wrapper" class="gray-bg">
            <div class="row border-bottom white-bg">
                <nav class="navbar navbar-expand-lg navbar-static-top" role="navigation">
                    <!--<div class="navbar-header">-->
                        <!--<button aria-controls="navbar" aria-expanded="false" data-target="#navbar" data-toggle="collapse" class="navbar-toggle collapsed" type="button">-->
                            <!--<i class="fa fa-reorder"></i>-->
                            <!--</button>-->

                            <a href="#" class="navbar-brand " style="padding: 3px 15px;"><h1 class="mp0 gras" style="font-size: 45px">GPV</h1></a>
                            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-label="Toggle navigation">
                                <i class="fa fa-reorder"></i>
                            </button>

                            <!--</div>-->
                            <div class="navbar-collapse collapse" id="navbar">
                                <ul class="nav navbar-nav mr-auto">
                                    <li class="gras <?= (isJourFerie(dateAjoute(1)))?"text-red":"text-muted" ?>">
                                        <span class="m-r-sm welcome-message text-uppercase" id="date_actu"></span> 
                                        <span class="m-r-sm welcome-message gras" id="heure_actu"></span> 
                                    </li>

                                </ul>
                                <a id="onglet-master" href="<?= $this->url("config", "master", "dashboard") ?>" class="onglets btn btn-xs btn-white" style="font-size: 12px; margin-right: 10px;"><i class="fa fa-long-arrow-left"></i> Retour au tableau de bord</a>
                            </div>
                        </nav>
                    </div>

                    <br>
                    <div class="wrapper-content">
                        <div class="animated fadeInRightBig container-fluid">

                            <div class="row">

                                <div class="col-md-12 bloc">
                                    <div class="ibox border">
                                        <div class="ibox-title">
                                            <h5 class="text-uppercase">Ceux que vous produisez</h5>
                                            <div class="ibox-tools">

                                            </div>
                                        </div>
                                        <div class="ibox-content">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <?php foreach ($parfums as $key => $type) {  ?>
                                                            <td class="gras text-center"> <?= $type->name(); ?></td>
                                                        <?php } ?>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($types as $key => $prod) { ?> 
                                                        <tr>
                                                            <td style="width: 250px" class="gras text-uppercase"> <?= $prod->name(); ?></td>

                                                            <?php 
                                                            foreach ($parfums as $key => $parfum) {
                                                                $item = Home\TYPEPRODUIT_PARFUM::findBy(["typeproduit_id ="=>$prod->id, "parfum_id ="=>$parfum->id])[0]; $item->actualise(); ?>
                                                                <td class="text-center">
                                                                    <span><?= $item->name();  ?></span><br>
                                                                    <div class="switch" style="display: inline-block;">
                                                                        <div class="onoffswitch">
                                                                            <input type="checkbox" <?= ($item->isActive())?"checked":""  ?> onchange='changeActive("typeproduit_parfum", <?= $item->id ?>)' class="onoffswitch-checkbox" id="typeproduit_parfum<?= $item->id ?>">
                                                                            <label class="onoffswitch-label" for="typeproduit_parfum<?= $item->id ?>">
                                                                                <span class="onoffswitch-inner"></span>
                                                                                <span class="onoffswitch-switch"></span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            <?php } ?>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>




                                <div class="col-md-12 bloc">
                                    <div class="ibox border">
                                        <div class="ibox-title">
                                            <h5 class="text-uppercase">Exigence de production par ressource</h5>
                                            <div class="ibox-tools">
                                                <div class="switch" title="Appliquer le barème de production">
                                                    <div class="onoffswitch inline-block">
                                                        <input type="checkbox" <?= ($params->productionAuto == Home\TABLE::OUI)?"checked":""  ?> onchange='changeProductionAuto()' class="onoffswitch-checkbox" id="productionAuto">
                                                        <label class="onoffswitch-label" for="productionAuto">
                                                            <span class="onoffswitch-inner"></span>
                                                            <span class="onoffswitch-switch"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ibox-content">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <?php foreach ($ressources as $key => $ressource) {  ?>
                                                            <td class="gras text-center"> <?= $ressource->name(); ?></td>
                                                        <?php } ?>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($types_parfums as $key => $prod) {
                                                        $lots = $prod->fourni('exigenceproduction');
                                                        if (count($lots) > 0) {
                                                            $exi = $lots[0];
                                                            $exi->actualise();
                                                            $lots = $exi->fourni('ligneexigenceproduction');   ?> 
                                                            <tr>
                                                                <td style="width: 250px" class="gras text-uppercase"> <?= $prod->name(); ?></td>
                                                                <?php foreach ($ressources as $key => $ressource) {
                                                                    $item = Home\LIGNEEXIGENCEPRODUCTION::findBy(["exigenceproduction_id ="=>$exi->id, "ressource_id ="=>$ressource->id])[0]; 
                                                                    if ($item->quantite > 0) {
                                                                        $item->actualise(); ?>
                                                                        <td class="text-center <?= ($prod->ressource_id == $ressource->id)?"gras text-blue":""  ?> "><?= money($item->quantite); ?> <?= $item->ressource->abbr ?></td>
                                                                    <?php  }else{ ?>
                                                                        <td></td>
                                                                    <?php }
                                                                } ?>
                                                                <td>==></td>
                                                                <td class="text-blue"> Pour <b><?= $exi->quantite  ?></b> <?= $prod->typeproduit->abbr ?></td>
                                                                <td>
                                                                    <i class="fa fa-pencil cursor" data-toggle="modal" data-target="#modal-exigence<?= $prod->id ?>"> </i>

                                                                    <button onclick="modification('typeproduit_parfum', <?= $prod->id ?>)" class="btn btn-xs btn-white text-blue" title="Changer la ressource principale"><i class="fa fa-cube" data-toggle="modal" data-target="#modal-ressourceprincipale"> </i></button>
                                                                </td>
                                                            </tr>
                                                        <?php } 
                                                    } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>


                        </div>
                    </div>
                </div>

                <br>

                <?php include($this->rootPath("webapp/config/elements/templates/footer.php")); ?>


            </div>
        </div>


        <?php include($this->rootPath("webapp/config/elements/templates/script.php")); ?>

        <?php include($this->rootPath("composants/assets/modals/modal-params.php") );  ?>
        <?php include($this->relativePath("modals.php")); ?>


    </body>



    </html>