<!DOCTYPE html>
<html>

<?php include($this->rootPath("webapp/manager/elements/templates/head.php")); ?>


<body class="top-navigation">

    <div id="wrapper">



        <div id="page-wrapper" class="gray-bg">



          <div class="wrapper wrapper-content animated fadeInRight article">
            <div class="row justify-content-md-center">
                <div class="col-lg-10">
                    <div class="ibox"  >
                        <div class="ibox-content"  style="min-height: 33cm; background-image: url(<?= $this->stockage("images", "societe", "filigrane.png")  ?>) ; background-size: 50%; background-position: center center; background-repeat: no-repeat;">

                            <div>
                                <div class="row">
                                    <div class="col-sm-5">
                                        <div class="row">
                                            <div class="col-3">
                                                <img style="width: 120%" src="<?= $this->stockage("images", "societe", $params->image) ?>">
                                            </div>
                                            <div class="col-9">
                                                <h5 class="gras text-uppercase text-orange"><?= $params->societe ?></h5>
                                                <h5 class="mp0"><?= $params->adresse ?></h5>
                                                <h5 class="mp0"><?= $params->postale ?></h5>
                                                <h5 class="mp0">Tél: <?= $params->contact ?></h5>
                                                <h5 class="mp0">Email: <?= $params->email ?></h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-7 text-right">
                                        <h2 class="title text-uppercase gras text-blue"><?= $commande->typecommande->name() ?></h2>
                                        <h3 class="text-uppercase">N°<?= $commande->reference  ?></h3>
                                        <h5><?= datelong($commande->created)  ?></h5>  
                                        <h4><small>Bon édité par :</small> <span class="text-uppercase"><?= $commande->employe->name() ?></span></h4>
                                    </div>
                                </div><hr class="mp3">

                                <div class="row">
                                    <div class="col-6">
                                        <h5><span>Zone de livraison :</span> <span class="text-uppercase"><?= $commande->zonedevente->name() ?></span></h5>   
                                        <h5><span>Lieu de livraison :</span> <span class="text-uppercase"><?= $commande->lieu ?></span></h5>   
                                        <?php if ($commande->typebareme_id == Home\TYPEBAREME::GROS) { ?>
                                            <h5 class="text-uppercase text-blue">vente au <?= $commande->typebareme->name() ?></span></h5>
                                        <?php } ?>     

                                    </div>

                                    <div class="col-6 text-right">
                                        <h5><span>Client :</span> <span class="text-uppercase"><?= $commande->groupecommande->client->name() ?></span></h5>
                                        <h5><span>Livraison prévue pour le:</span> <span class="text-uppercase"><?= datecourt($commande->datelivraison) ?></span></h5><br>

                                        <?php if ($commande->typecommande_id == Home\TYPECOMMANDE::AUTOSHIP) { ?>
                                            <h4><span>Code / Pseudo / KSCI :</span> <span class="text-uppercase text-orange"><?= $commande->code ?></span></h4>                   
                                        <?php } ?>
                                    </div>
                                </div><br><br>

                                <table class="table table-striped">
                                    <thead class="text-uppercase" style="background-color: #dfdfdf">
                                        <tr class="text-center">
                                            <th colspan="2"></th>
                                            <th>Prix unitaire</th>
                                            <th>Quantité</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($commande->lignecommandes as $key => $ligne) {
                                            $ligne->actualise(); ?>
                                            <tr>
                                                <td class="desc">
                                                    <h5 class="mp0 text-uppercase gras"><?= $ligne->produit->name() ?></h5>
                                                    <img style="height: 20px" src="<?= $this->stockage("images", "emballages", $ligne->emballage->image) ?>" >
                                                    <small><?= $ligne->emballage->name() ?></small>
                                                </td>
                                                <td></td>
                                                <td class="text-center">
                                                    <h4 class="text-muted"><?= money($ligne->price / $ligne->quantite) ?> <?= $params->devise ?></h4>
                                                </td>
                                                <td class="text-center"><h3 style="font-weight: 300px"><i>x <?= $ligne->quantite ?></i></h3></td>
                                                <td class="text-center" width="25%">
                                                    <h3 class="gras"><?= money($ligne->price) ?> <?= $params->devise ?></h3>
                                                </td>
                                            </tr>
                                        <?php } ?> 
                                        <tr style="height: 20px;"></tr>
                                        <tr style="background-color: #fff">
                                            <td colspan="3" class="text-uppercase text-right"><h4 class="">Montant Total = </h4></td>
                                            <td></td>
                                            <td colspan="1" class="text-center"><h2 class="text-muted"><?= money($commande->montant + $commande->reduction - $commande->tva) ?> <?= $params->devise ?></h2></td>
                                        </tr>
                                        <tr style="background-color: #fff">
                                            <td colspan="3" class="text-uppercase text-right"><h4 class="">TVA (<?= $commande->taux_tva ?>%) = </h4></td>
                                            <td></td>
                                            <?php if ($commande->sousTVA == Home\TABLE::OUI) { ?>
                                                <td colspan="1" class="text-center"><h4 class="text-muted"><?= money($commande->tva) ?> <?= $params->devise ?></h4></td>
                                            <?php } else{ ?>
                                                <td colspan="1" class="text-center"><h4 class="text-muted">TVA non appliquée</h4></td>
                                            <?php } ?>
                                        </tr>
                                        <tr style="background-color: #fff">
                                            <td colspan="3" class="text-uppercase text-right"><h4 class="">Reduction = </h4></td>
                                            <td></td>
                                            <td colspan="1" class="text-center"><h4 class="text-muted"><?= money($commande->reduction) ?> <?= $params->devise ?></h4></td>
                                        </tr>

                                        <tr style="height: 35px;"></tr>

                                        <tr class="border">
                                            <td colspan="3" class="text-uppercase text-right"><h2 class="">Net à payer = </h2></td>
                                            <td></td>
                                            <td colspan="1" class="text-center"><h2 class="gras text-success"><?= money($commande->montant) ?> <?= $params->devise ?></h2></td>
                                        </tr>

                                        <tr class="border">
                                            <td colspan="3" class="text-right">
                                                <h3 class="text-uppercase mp0">Avance sur montant = </h3>
                                                <?php if (!isset($reglement)) { ?>
                                                    <small>Réglement par prélèvement sur acompte</small>
                                                <?php }else{ ?>
                                                    <small>Réglement par <?= $reglement->modepayement->name() ?></small>
                                                <?php } ?>

                                            </td>
                                            <td></td>
                                            <td colspan="1" class="text-center"><h3 class="gras text-"><?= money($commande->avance) ?> <?= $params->devise ?></h3></td>
                                        </tr>
                                        <tr class="border">
                                            <td colspan="3" class="text-uppercase text-right"><h4 class=" text-<?= ($commande->reste > 0) ? "warning":"muted"  ?> ">reste à payer pour cette commande = </h4></td>
                                            <td></td>
                                            <td colspan="1" class="text-center"><h3 class="gras text-<?= ($commande->reste > 0) ? "warning":"muted"  ?>"><?= money($commande->reste) ?> <?= $params->devise ?></h3></td>
                                        </tr>

                                        <tr style="height: 45px;"></tr>

                                        <tr class="border">
                                            <td colspan="3" class="text-right">
                                                <h4 class="text-uppercase mp0">Solde de l'acompte du client =</h4>
                                            </td>
                                            <td></td>
                                            <td colspan="1" class="text-center"><h3 class="gras text-"><?= money($commande->acompteClient) ?> <?= $params->devise ?></h3></td>
                                        </tr>
                                        <tr class="border">
                                            <td colspan="3" class="text-uppercase text-right"><h4 class=" text-red ">Dette totale du client = </h4></td>
                                            <td></td>
                                            <td colspan="1" class="text-center"><h3 class="gras text-<?= ($commande->reste > 0) ? "danger":"muted"  ?>"><?= money($commande->detteClient) ?> <?= $params->devise ?></h3></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <br><br><br>
                                <div class="row text-center" style="margin-top: -2%">
                                    <div class="offset-9 col-3" style="padding-top: 0.5%; height: 100px;">
                                        <span><u>Signature & Cachet</u></span>
                                    </div>
                                </div>
                            </div>


                            <br><br><hr class="mp0">
                            <p class="text-center"><small><i>* Nous vous prions de vérifier l'exactitude de toutes les informations qui ont été mentionnées sur cette facture avant de quitter nos locaux !</i></small></p>



                        </div>
                    </div>

                </div>
            </div>


        </div>


        <?php include($this->rootPath("webapp/manager/elements/templates/footer.php")); ?>


    </div>
</div>


<?php include($this->rootPath("webapp/manager/elements/templates/script.php")); ?>

<button class="btn btn-outline-primary btn-rounded btn-xs d-print-none" onclick="window.print()" style="position: fixed; bottom: 8%; right: 2%; z-index: 8000"><i class="fa fa-print"></i> Imprimer la fiche</button>

</body>

</html>
