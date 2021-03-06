<!DOCTYPE html>
<html>

<?php include($this->rootPath("webapp/boutique/elements/templates/head.php")); ?>


<body class="fixed-sidebar">

    <div id="wrapper">

        <?php include($this->rootPath("webapp/boutique/elements/templates/sidebar.php")); ?>  

        <div id="page-wrapper" class="gray-bg">

            <?php include($this->rootPath("webapp/boutique/elements/templates/header.php")); ?>  


            <div class="wrapper-content">
                <div class="animated fadeInRightBig">

                  <?php foreach ($typeproduits as $key => $type) { ?>
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5 class="text-uppercase">Stock de <?= $type->name() ?></h5>
                        </div>
                        <div class="ibox-content">
                            <div class="row">
                                <?php foreach ($type->fourni("typeproduit_parfum", ["isActive ="=>Home\TABLE::OUI]) as $key => $pro) {
                                    $pro->actualise(); ?>
                                    <div class="col-md-6 border-right">
                                        <h6 class="text-uppercase text-center gras" ><?= $pro->name() ?> </h6>
                                        <ul class="list-group clear-list m-t">
                                            <?php foreach ($pro->fourni("produit", ["isActive ="=>Home\TABLE::OUI]) as $key => $produit) {
                                                $produit->actualise();  ?>
                                                <li class="list-group-item ">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <h5><i class="fa fa-flask"></i> <?= $produit->quantite->name() ?></h5>
                                                            <a class="btn btn-white btn-xs " href="<?= $this->url("boutique", "stock", "produits", $produit->id)  ?>">+ de details</a>
                                                        </div>  
                                                        <div class="col-md-9">
                                                            <div class="row text-center">
                                                                <?php foreach ($produit->getListeEmballageProduit() as $key => $emballage) {
                                                                    $a = $produit->enBoutique(Home\PARAMS::DATE_DEFAULT, dateAjoute(1), $emballage->id, $boutique->id);
                                                                    if ($a > 0) {
                                                                        $prods[] = $produit; ?>
                                                                        <div class="col-sm-4 cursor border-right border-bottom">
                                                                            <span class="gras <?= ($a >= $params->ruptureStock)?"":"text-red clignote" ?>"><?= start0($a) ?></span><br>
                                                                            <span class="">     
                                                                                <img style="height: 15px" src="<?= $this->stockage("images", "emballages", $emballage->image)  ?>"> <small><?= $emballage->name() ?></small>
                                                                            </span> 
                                                                        </div>
                                                                    <?php }
                                                                } ?> 
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php } ?>
                                            <li class="list-group-item"></li>
                                        </ul><br>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>

            </div>
        </div>


        <?php include($this->rootPath("webapp/boutique/elements/templates/footer.php")); ?>


        <button class="btn btn-outline-danger btn-rounded d-print-none" data-toggle="modal" data-target="#modal-perteboutique" style="position: fixed; bottom: 50px; right: 2%; z-index: 8000"><i class="fa fa-trash"></i> Perte en boutique</button>


    </div>
</div>


<?php include($this->rootPath("webapp/boutique/elements/templates/script.php")); ?>



<div class="modal inmodal fade" id="modal-perteboutique">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-red">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Enregistrer une perte</h4>
                <small>Veuillez renseigner les informations pour enregistrer la perte</small>
            </div>
            <form method="POST" class="formShamman" classname="perteboutique">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <label>Production perdue <span1>*</span1></label>
                            <div class="form-group">
                                <?php Native\BINDING::html("select", "produit"); ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <label>Type d'emballage <span1>*</span1></label>
                            <div class="form-group">
                                <?php Native\BINDING::html("select", "emballage"); ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <label>Quantité perdue <span1>*</span1></label>
                            <div class="form-group">
                                <input type="number" number class="form-control" name="quantite" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <label>Cause de la perte <span1>*</span1></label>
                            <div class="form-group">
                                <?php Native\BINDING::html("select", "typeperte"); ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label>Plus de détails <span1>*</span1></label>
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
