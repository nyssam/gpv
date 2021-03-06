
<div class="modal inmodal fade" id="modal-livraison<?= $livraison->id ?>" style="z-index: 99999999">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title">Validation de la livraison</h4>
            <small class="font-bold">Renseigner ces champs pour terminer la livraison</small>
        </div>
        
        <form class="formValiderLivraison">
            <div class="row">
                <div class="col-md-8">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5 class="text-uppercase">Les produits effectivement réçus</h5>
                        </div>
                        <div class="ibox-content"><br>
                            <div class="table-responsive">
                                <table class="table  table-striped">
                                    <tbody class="commande">
                                        <?php foreach ($livraison->ligneprospections as $key => $ligne) { ?>
                                            <tr class="border-0 border-bottom" data-id="<?= $ligne->id ?>">
                                                <td >
                                                    <span class="small gras"><?= $ligne->produit->name() ?></span><br>
                                                    <img style="height: 20px" src="<?= $this->stockage("images", "emballages", $ligne->emballage->image) ?>" >
                                                    <small><?= $ligne->emballage->name() ?></small>
                                                </td>
                                                <td class="text-center">
                                                    <br>
                                                    <h4 class="mp0 text-uppercase"><?= $ligne->quantite ?></h4>
                                                </td>
                                                <td width="110" class="text-center">
                                                    <small>Quantité livrée</small>
                                                    <input type="text"  data-id="<?= $ligne->id ?>" number class="form-control text-center gras vendus input-sm" value="<?= $ligne->quantite ?>" max="<?= $ligne->quantite ?>">
                                                </td>
                                                <td  width="30"></td>
                                                <td width="100" class="text-center text-red">
                                                    <small>Perte</small>
                                                    <input type="text" number class="form-control text-center gras perdus input-xs" value="0" max="<?= $ligne->quantite ?>">
                                                </td>
                                            </tr>
                                        <?php }  ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-md-4 ">
                    <div class="ibox"  style="background-color: #eee">
                        <div class="ibox-title" style="padding-right: 2%; padding-left: 3%; ">
                            <h5 class="text-uppercase">Finaliser la livraison</h5>
                        </div>
                        <div class="ibox-content"  style="background-color: #fafafa">
                            <!-- <div>
                                <label>date de livraison <span style="color: red">*</span> </label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="date" name="datelivraison" class="form-control" value="<?= dateAjoute() ?>" required>
                                </div>
                            </div><br> -->
                            <div>
                                <label>Nom du receptionniste <span style="color: red">*</span> </label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                    <input type="text" value="<?= $livraison->groupecommande->client->name() ?>" name="nom_receptionniste" class="form-control" required>
                                </div>
                            </div><br>
                            <div>
                                <label>Contact du receptionniste </label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                    <input type="text" value="<?= $livraison->groupecommande->client->contact ?>" name="contact_receptionniste" class="form-control">
                                </div>
                            </div><br>
                            <div>
                                <label>Commentaire </label>
                                <textarea class="form-control" rows="4" name="comment"></textarea>
                            </div><br>
                            <button class="btn btn-primary btn-block dim"><i class="fa fa-check"></i> Terminer la livraison</button>
                        </div>
                    </div>

                </div>
            </div>
        </form>

    </div>
</div>
</div>


