
<div class="modal inmodal fade" id="modal-newlivraison" style="z-index: 99999999">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title">Nouveau bon de livraison</h4>
            <small class="font-bold">Renseigner ces champs pour enregistrer la livraison</small>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5 class="text-uppercase">Les produits de la livraison</h5>
                    </div>
                    <div class="ibox-content"><br>
                        <div class="table-responsive">
                            <table class="table  table-striped">
                                <tbody class="commande">
                                    <?php
                                    $datas = $groupecommande->toutesLesLignes();
                                    foreach ($datas as $key => $lig) {
                                        $reste = $groupecommande->reste($lig->produit_id, $lig->emballage_id);
                                        if ($reste > 0) {
                                            $produit = new Home\PRODUIT;
                                            $produit->id = $lig->produit_id;
                                            $produit->actualise();

                                            $emballage = new Home\EMBALLAGE;
                                            $emballage->id = $lig->emballage_id;
                                            $emballage->actualise();
                                            $a = time()  ?>
                                            <tr class="border-0 border-bottom " id="ligne<?= $a ?>" data-id="<?= $produit->id ?>" data-format="<?= $emballage->id ?>">
                                                <td><i class="fa fa-close text-red cursor" onclick="supprimeProduit(<?= $a ?>)" style="font-size: 18px;"></i></td>
                                                <td >
                                                    <span class="small gras"><?= $produit->name() ?></span><br>
                                                    <img style="height: 20px" src="<?= $rooter->stockage("images", "emballages", $emballage->image) ?>" >
                                                    <small><?= $emballage->name() ?></small>
                                                </td>
                                                <td class="text-center">
                                                    <br>
                                                    <h4 class="mp0 text-uppercase"><?= $reste ?></h4>
                                                </td>
                                                <td width="30"></td>
                                                <td width="120" class="text-center">
                                                    <small>Qté à livrer</small>
                                                    <input type="text" number class="form-control text-center gras" value="<?= $reste ?>" max="<?= $reste ?>">
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
            <div class="col-md-4 ">
                <div class="ibox"  style="background-color: #eee">
                    <div class="ibox-title" style="padding-right: 2%; padding-left: 3%; ">
                        <h5 class="text-uppercase">Finaliser la livraison</h5>
                    </div>
                    <div class="ibox-content"  style="background-color: #fafafa">
                        <form id="formLivraison">
                            <div>
                                <label>zone de livraison <span style="color: red">*</span> </label>
                                <div class="input-group">
                                    <select class="select2 form-control" name="zonedevente_id" style="width: 100%">
                                        <?php 
                                        $datas = $groupecommande->commandes;
                                        $datas2 = $dt = [];
                                        foreach ($datas as $key => $value) {
                                            if (!in_array($value->zonedevente_id, $dt)) {
                                                $dt[] = $value->zonedevente_id;
                                                $datas2[] = $datas[$key];
                                            }
                                        }
                                        foreach ($datas2 as $key => $commande) {
                                            $commande->actualise(); ?>
                                            <option value="<?= $commande->zonedevente_id ?>"><?= $commande->zonedevente->name()  ?></option>
                                        <?php } ?>                                        
                                    </select>
                                </div>
                            </div><br>
                            <div>
                                <label>Lieu de livraison <span style="color: red">*</span> </label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-map-marker"></i></span><input type="text" name="lieu" class="form-control" required>
                                </div>
                            </div><br>

                            <div>
                                <label>Livré par <span style="color: red">*</span> </label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-usesr"></i></span>
                                    <input type="text" name="livreur" class="form-control" >
                                </div>
                            </div><br>


                            <div>
                                <label>Frais de transport pour livraison <span style="color: red">*</span> </label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-money"></i></span>
                                    <input type="text" name="transport" class="form-control" value="0">
                                </div>
                            </div><br>

                       <!--  <div class="chauffeur">
                            <label>Chauffeur de la livraison <span style="color: red">*</span> </label>                                
                            <div class="input-group">
                                <?php // Native\BINDING::html("select-tableau", Home\CHAUFFEUR::libres(), null, "chauffeur_id"); ?>
                            </div><br>
                        </div>

                        <div class="location">
                            <label>Location de véhicule <span style="color: red">*</span> </label>  
                            <div class="input-group">
                                <select class="form-control select2" name="isLouer" style="width: 100%">
                                    <option value="0">Non, pas de location de véhicule</option>
                                    <option value="1">Oui, faire louer véhicule</option>
                                </select>
                            </div>
                        </div><br>
                        <div class="montant_location">
                            <label class="gras">Montant de la location <span style="color: red">*</span> </label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-money"></i></span><input type="number" number name="montant_location" class="form-control" value="0" min="0">
                            </div><br>
                            <div class="no_modepayement_facultatif">
                                <div>
                                    <label>Montant avancé pour réglement<span style="color: red">*</span> </label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-money"></i></span><input type="text" value="0" min="0" name="avance" class="form-control">
                                    </div>
                                </div>
                            </div><br>
                            <div>
                                <label>Mode de payement <span style="color: red">*</span> </label>                                
                                <div class="input-group">
                                    <?php //Native\BINDING::html("select", "modepayement"); ?>
                                </div>
                            </div><br>
                            <div class="modepayement_facultatif">
                                <div>
                                    <label>Structure d'encaissement<span style="color: red">*</span> </label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-bank"></i></span><input type="text" name="structure" class="form-control">
                                    </div>
                                </div><br>
                                <div>
                                    <label>N° numero dédié<span style="color: red">*</span> </label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-pencil"></i></span><input type="text" name="numero" class="form-control">
                                    </div>
                                </div>
                            </div><br>
                        </div>

                        <div class="tricycle">
                            <div>
                                <label>Nom & prénom du chauffeur tricycle<span style="color: red">*</span> </label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-user"></i></span><input type="text" name="nom_tricycle" class="form-control" >
                                </div>
                            </div><br>
                            <div>
                                <label>Montant à payer au chauffeur tricycle<span style="color: red">*</span> </label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-money"></i></span><input type="text" name="paye_tricycle" class="form-control" value="0" min="0" >
                                </div>
                            </div>
                        </div><br>

                        <div>
                            <label><input class="i-checks cursor" type="checkbox" name="chargement_manoeuvre" checked > Chargement par nos manoeuvres</label>
                            <label><input class="i-checks cursor" type="checkbox" name="dechargement_manoeuvre" checked > Déchargement par nos manoeuvres</label>
                        </div><br>
                    -->
                    <input type="hidden" name="client_id" value="<?= $groupecommande->client_id ?>">
                    <input type="hidden" name="typeprospection_id" value="<?= Home\TYPEPROSPECTION::LIVRAISON ?>" class="form-control">
                    <input type="hidden" name="commercial_id" value="<?= Home\COMMERCIAL::MAGASIN ?>" class="form-control">

                </form>
                <hr/>
                <button onclick="validerLivraison()" class="btn btn-primary btn-block dim"><i class="fa fa-check"></i> Valider la livraison</button>
            </div>
        </div>

    </div>
</div>

</div>
</div>
</div>


