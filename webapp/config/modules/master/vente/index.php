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

                            <div class="ibox border">
                                <div class="ibox-title">
                                    <h5 class="text-uppercase">Tranche des prix par produit et par emballage dans vos boutiques</h5>
                                </div>
                                <div class="ibox-content">
                                    <div class="row">
                                        <?php $i =0; foreach ($types_parfums as $key => $type) { ?>
                                            <div class="col-md-6" style="margin-bottom: 3%">
                                                <table class="table table-striped table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th colspan="2" class="text-uppercase text-center"><?= $type->name(); ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $i =0; 
                                                        foreach ($type->fourni("produit") as $key => $produit) {
                                                            $produit->actualise(); ?>
                                                            <tr>
                                                                <td class="" style="width: 30%">
                                                                    <?= $produit->name(); ?> 
                                                                    <div class="row">
                                                                        <div class="col-sm-6">
                                                                            <div class="onoffswitch">
                                                                                <input type="checkbox" <?= ($produit->isActive())?"checked":""  ?> onchange='changeActive("produit", <?= $produit->id ?>)' class="onoffswitch-checkbox" id="produit<?= $produit->id ?>">
                                                                                <label class="onoffswitch-label" for="produit<?= $produit->id ?>">
                                                                                    <span class="onoffswitch-inner"></span>
                                                                                    <span class="onoffswitch-switch"></span>
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td class="text-center">
                                                                    <div class="row">
                                                                        <?php if ($produit->isActive()) {
                                                                           foreach ($produit->getListeEmballageProduit() as $key => $emballage) {
                                                                            $prix = $produit->fourni("price", ["emballage_id ="=>$emballage->id])[0] ?>
                                                                            <div class="col-md border-right">
                                                                                <div class="" style="color: blue">
                                                                                    <img style="height: 20px" src="<?= $this->stockage("images", "emballages", $emballage->image)  ?>"> <small><?= $emballage->name(); ?></small>
                                                                                </div><hr class="mp3">
                                                                                <div class="price">
                                                                                    <div class="">
                                                                                        <input type="text" title="Prix Unitaire normal" style="font-size: 10px; padding: 3px" number class="form-control input-xs text-center prix" value="<?= $prix->prix ?>" name="prix" id="<?= $prix->id ?>">
                                                                                    </div>
                                                                                    <div class="" style=" color: orangered">
                                                                                        <input type="text" title="Prix unitaire de gros" style="font-size: 10px; padding: 3px" number class="form-control input-xs text-center prix_gros" value="<?= $prix->prix_gros ?>" name="prix_gros" id="<?= $prix->id ?>">
                                                                                    </div>
                                                                                    <div class="" style=" color: blue">
                                                                                        <input type="text" title="Prix unitaire spécial personnel" style="font-size: 10px; padding: 3px" number class="form-control input-xs text-center prix_special" value="<?= $prix->prix_special ?>" name="prix_special" id="<?= $prix->id ?>">
                                                                                    </div>
                                                                                    <div class="" style=" color: navy">
                                                                                        <input type="text" title="Prix unitaire autoship" style="font-size: 10px; padding: 3px" number class="form-control input-xs text-center prix_special" value="<?= $prix->prix_autoship ?>" name="prix_autoship" id="<?= $prix->id ?>">
                                                                                    </div>
                                                                                    <div class="" style=" color: green">
                                                                                        <input type="text" title="Prix unitaire de pour inscription" style="font-size: 10px; padding: 3px" number class="form-control input-xs text-center prix_gros" value="<?= $prix->prix_inscription ?>" name="prix_inscription" id="<?= $prix->id ?>">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        <?php }
                                                                    } ?>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        
                        <div class="row">
                            <div class="col-sm-7 bloc">
                                <div class="ibox border">
                                    <div class="ibox-title">
                                        <h5 class="text-uppercase">Les paliers de reductions</h5>
                                        <div class="ibox-tools">
                                            <button class="btn_modal btn btn-xs btn-white" data-toggle="modal" data-target="#modal-palier">
                                                <i class="fa fa-plus"></i> Ajouter
                                            </button>
                                        </div>
                                    </div>
                                    <div class="ibox-content">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Libéllé</th>
                                                    <th>Prix min</th>
                                                    <th>Prix max</th>
                                                    <th>Reduction</th>
                                                    <th></th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (Home\PALIER::getAll() as $key => $item) {
                                                    $item->actualise(); ?>
                                                    <tr>
                                                        <td class="gras"><?= $item->name(); ?></td>
                                                        <td><?= money($item->min) ?> <?= $params->devise ?></td>
                                                        <td><?= money($item->max) ?> <?= $params->devise ?></td>
                                                        <td><?= $item->reduction; ?> <?= ($item->typereduction_id == Home\TYPEREDUCTION::BRUT)?$params->devise:"%"  ?></td>
                                                        <td data-toggle="modal" data-target="#modal-palier" title="modifier l'élément" onclick="modification('palier', <?= $item->id ?>)"><i class="fa fa-pencil text-blue cursor"></i></td>
                                                        <td title="supprimer le palier" onclick="suppressionWithPassword('palier', <?= $item->id ?>)"><i class="fa fa-close cursor text-danger"></i></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>


                            <div class="col-sm-5 bloc">
                                <div class="ibox border">
                                    <div class="ibox-title">
                                        <h5 class="text-uppercase">Les zones de vente</h5>
                                        <div class="ibox-tools">
                                            <button class="btn_modal btn btn-xs btn-white" data-toggle="modal" data-target="#modal-zonedevente">
                                                <i class="fa fa-plus"></i> Ajouter
                                            </button>
                                        </div>
                                    </div>
                                    <div class="ibox-content">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Libéllé</th>
                                                    <th></th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $i =0; foreach (Home\ZONEDEVENTE::findBy([], [], ["name"=>"ASC"]) as $key => $item) { ?>
                                                    <tr>
                                                        <td class="gras"><?= $item->name(); ?></td>
                                                        <td data-toggle="modal" data-target="#modal-zonedevente" title="modifier la zone de livraison" onclick="modification('zonedevente', <?= $item->id ?>)"><i class="fa fa-pencil text-blue cursor"></i></td>
                                                        <td title="supprimer la zone de livraison" onclick="suppressionWithPassword('zonedevente', <?= $item->id ?>)"><i class="fa fa-close cursor text-danger"></i></td>
                                                    </tr>
                                                <?php } ?>
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

                <?php include($this->relativePath("modals.php")); ?>

            </div>
        </div>


        <?php include($this->rootPath("webapp/config/elements/templates/script.php")); ?>

    </body>



    </html>