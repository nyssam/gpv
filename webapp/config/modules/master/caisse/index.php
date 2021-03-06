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
                                <div class="col-sm-6 bloc">
                                    <div class="ibox border">
                                        <div class="ibox-title">
                                            <h5 class="text-uppercase">Opérations d'entrée de caisse</h5>
                                            <div class="ibox-tools">
                                                <a class="btn_modal" data-toggle="modal" data-target="#modal-categorieoperation">
                                                    <i class="fa fa-plus"></i> Ajouter
                                                </a>
                                            </div>
                                        </div>
                                        <div class="ibox-content">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th><i class="fa fa-ticket"></i></th>
                                                        <th>Libéllé</th>
                                                        <th>Type</th>
                                                        <th></th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $i =0; foreach (Home\CATEGORIEOPERATION::findBy(["typeoperationcaisse_id ="=>Home\TYPEOPERATIONCAISSE::ENTREE], [], ["typeoperationcaisse_id"=>"ASC", "name"=>"ASC"]) as $key => $item) {
                                                        $item->actualise();
                                                        $i++; ?>
                                                        <tr>
                                                            <td><?= $i ?></td>
                                                            <td><div class="border" style="width: 20px; height: 20px; background-color: <?= $item->color ?>"></div></td>
                                                            <td class="gras"><?= $item->name(); ?></td>
                                                            <td class="gras text-<?= ($item->typeoperationcaisse_id == Home\TYPEOPERATIONCAISSE::ENTREE)?"green":"red"  ?>"><?= $item->typeoperationcaisse->name(); ?></td>
                                                            <td data-toggle="modal" data-target="#modal-categorieoperation" title="modifier la categorie" onclick="modification('categorieoperation', <?= $item->id ?>)"><i class="fa fa-pencil text-blue cursor"></i></td>
                                                            <td title="supprimer la categorie" onclick="suppressionWithPassword('categorieoperation', <?= $item->id ?>)"><i class="fa fa-close cursor text-danger"></i></td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div><hr>

                                    <div class="">
                                        <form method="POST" class="formShamman" classname="params" reload="false">
                                            <div class="row">
                                                <div class="col-8 gras">Autoriser Versements en attente</div>
                                                <div class="offset-1"></div>
                                                <div class="col-3">
                                                    <div class="switch d-block">
                                                        <div class="onoffswitch">
                                                            <input type="checkbox" name="autoriserVersementAttente" <?= ($params->autoriserVersementAttente == "on")?"checked":""  ?> class="onoffswitch-checkbox" id="example2">
                                                            <label class="onoffswitch-label" for="example2">
                                                                <span class="onoffswitch-inner"></span>
                                                                <span class="onoffswitch-switch"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div><br>

                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <label>% tva sur les ventes</label>
                                                    <input type="number" number class="form-control" name="tva" value="<?= $params->tva ?>">
                                                </div>
                                                <div class="col-sm-4">
                                                    <label>Es-ce une TVA active ?</label>
                                                    <select class="select2 form-control" name="tvaActive" style="width: 100%">
                                                        <option <?= ($params->tvaActive ==  Home\TABLE::OUI)?"selected":"" ?> value="<?= Home\TABLE::OUI ?>">Oui, Tva active</option>
                                                        <option <?= ($params->tvaActive ==  Home\TABLE::NON)?"selected":"" ?> value="<?= Home\TABLE::NON ?>">Non, Tva passive</option>
                                                    </select>
                                                </div>
                                                <div class="col-sm-4">
                                                    <label>Seuil crédit client </label>
                                                    <input type="number" number class="form-control" name="seuilCredit" value="<?= $params->seuilCredit ?>">
                                                </div>
                                            </div>


                                            <div>
                                                <br>
                                                <input type="hidden" name="id" value="<?= $params->id ?>">
                                                <button class="btn btn-primary dim "><i class="fa fa-check"></i> Mettre à jour</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="col-sm-6 bloc">
                                    <div class="ibox border">
                                        <div class="ibox-title">
                                            <h5 class="text-uppercase">Opérations de sortie de caisse</h5>
                                            <div class="ibox-tools">
                                                <a class="btn_modal" data-toggle="modal" data-target="#modal-categorieoperation">
                                                    <i class="fa fa-plus"></i> Ajouter
                                                </a>
                                            </div>
                                        </div>
                                        <div class="ibox-content">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th><i class="fa fa-ticket"></i></th>
                                                        <th>Libéllé</th>
                                                        <th>Type</th>
                                                        <th></th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $i =0; foreach (Home\CATEGORIEOPERATION::findBy(["typeoperationcaisse_id ="=>Home\TYPEOPERATIONCAISSE::SORTIE], [], ["typeoperationcaisse_id"=>"ASC", "name"=>"ASC"]) as $key => $item) {
                                                        $item->actualise();
                                                        $i++; ?>
                                                        <tr>
                                                            <td><?= $i ?></td>
                                                            <td><div class="border" style="width: 20px; height: 20px; background-color: <?= $item->color ?>"></div></td>
                                                            <td class="gras"><?= $item->name(); ?></td>
                                                            <td class="gras text-<?= ($item->typeoperationcaisse_id == Home\TYPEOPERATIONCAISSE::ENTREE)?"green":"red"  ?>"><?= $item->typeoperationcaisse->name(); ?></td>
                                                            <td data-toggle="modal" data-target="#modal-categorieoperation" title="modifier la categorie" onclick="modification('categorieoperation', <?= $item->id ?>)"><i class="fa fa-pencil text-blue cursor"></i></td>
                                                            <td title="supprimer la categorie" onclick="suppressionWithPassword('categorieoperation', <?= $item->id ?>)"><i class="fa fa-close cursor text-danger"></i></td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </div><br><hr>


                            <div class="ibox border">
                                <div class="ibox-title">
                                    <h5 class="text-uppercase">Creation & attribution des caisses</h5>
                                    <div class="ibox-tools">
                                        <a class="btn_modal" data-toggle="modal" data-target="#modal-comptebanque">
                                            <i class="fa fa-plus"></i> Nouveau compte
                                        </a>
                                    </div>
                                </div>
                                <div class="ibox-content">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Libéllé du compte</th>
                                                <th>Etablissement</th>
                                                <th>N° de compte</th>
                                                <th>Affiliation boutique</th>
                                                <th>Affiliation entrepôt</th>
                                                <th>Solde initial</th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i =0; foreach (Home\COMPTEBANQUE::getAll() as $key => $item) { ?>
                                                <tr>
                                                    <td class="gras"><?= $item->name(); ?></td>
                                                    <td class="gras"><?= $item->etablissement; ?></td>
                                                    <td class="gras"><?= $item->numero; ?></td>
                                                    <td>
                                                        <ul>
                                                            <?php foreach ($item->fourni("boutique") as $key => $elem) { ?>
                                                                <li><?= $elem->name() ?></li>
                                                            <?php } ?>
                                                        </ul>
                                                    </td>
                                                    <td>
                                                        <ul>
                                                            <?php foreach ($item->fourni("entrepot") as $key => $elem) { ?>
                                                                <li><?= $elem->name() ?></li>
                                                            <?php } ?>
                                                        </ul>
                                                    </td>
                                                    <td class="gras"><?= money($item->initial); ?> <?= $params->devise ?></td>
                                                    <td data-toggle="modal" data-target="#modal-comptebanque" title="modifier la categorie" onclick="modification('comptebanque', <?= $item->id ?>)"><i class="fa fa-pencil text-blue cursor"></i></td>
                                                    <td title="supprimer la categorie" onclick="suppressionWithPassword('comptebanque', <?= $item->id ?>)"><i class="fa fa-close cursor text-danger"></i></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>

                    <br>

                    <?php include($this->rootPath("webapp/config/elements/templates/footer.php")); ?>


                </div>
            </div>


            <?php include($this->rootPath("webapp/config/elements/templates/script.php")); ?>

            <?php include($this->rootPath("composants/assets/modals/modal-comptebanque.php") );  ?>


            <div class="modal inmodal fade" id="modal-categorieoperation">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                            <h4 class="modal-title">Formulaire des type d'operations</h4>
                        </div>
                        <form method="POST" class="formShamman" classname="categorieoperation">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-sm-5">
                                        <label>Type d'opération <span1>*</span1></label>
                                        <div class="form-group">
                                            <?php Native\BINDING::html("select", "typeoperationcaisse") ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-7">
                                        <label>Libéllé </label>
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="name" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="">
                                    <label>Couleur spécifique </label>
                                    <div class="form-group">
                                        <input type="color" name="color">
                                    </div>
                                </div>
                            </div><hr>
                            <div class="container">
                                <input type="hidden" name="id">
                                <button type="button" class="btn btn-sm  btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Annuler</button>
                                <button class="btn btn-sm btn-primary pull-right dim"><i class="fa fa-check"></i> enregistrer</button>
                            </div>
                            <br>
                        </form>
                    </div>
                </div>
            </div>

        </body>



        </html>