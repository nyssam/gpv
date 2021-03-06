
<div class="modal inmodal fade" id="modal-approemballage" style="z-index: 99999999">
    <div class="modal-dialog modal-xl" style="width: 95%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Nouvel approvisionnement d'emballage</h4>
                <small class="font-bold">Renseigner ces champs pour enregistrer l'approvisonnement </small>
            </div>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5 class="text-uppercase">Les produits de la commande</h5>
                        </div>
                        <div class="ibox-content"><br>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <tbody class="approvisionnement">
                                        <!-- rempli en Ajax -->
                                    </tbody>
                                </table>
                                <hr>
                                <div class=" text-center">
                                    <?php foreach (Home\EMBALLAGE::getAll() as $key => $item) {
                                        $item->actualise();
                                        if ($item->isActive() && $item->comptable == Home\TABLE::OUI) { ?>
                                            <button class="btn btn-white dim newressource text-capitalize" data-id="<?= $item->id ?>"><i class="fa fa-flask"></i> <?= $item->name(); ?></button>                   
                                        <?php  }
                                    }  ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 ">
                    <form id="formApprovisionnement" >
                        <div class="ibox"  style="background-color: #eee">
                            <div class="ibox-title" style="padding-right: 2%; padding-left: 3%; ">
                                <h5 class="text-uppercase">Finaliser l'approvisionnement </h5>
                            </div>
                            <div class="ibox-content"  style="background-color: #fafafa">
                                <div>
                                    <label>Le fournisseur <span style="color: red">*</span> </label>                                
                                    <div class="input-group">
                                        <?php Native\BINDING::html("select", "fournisseur"); ?>
                                    </div>
                                </div><br>
                                <div>
                                    <label>Etat de l'approvisionnement <span style="color: red">*</span> </label>                                
                                    <select class="select2 form-control" name="etat_id" style="width: 100%;">
                                        <option value="<?= Home\ETAT::ENCOURS ?>">Pas encore livré</option>
                                        <option value="<?= Home\ETAT::VALIDEE ?>">Déjà livré</option>
                                    </select>
                                </div><hr>
                                <div>
                                    <label>Mode de payement <span style="color: red">*</span> </label>                                
                                    <div class="input-group">
                                        <?php Native\BINDING::html("select", "modepayement"); ?>
                                    </div>
                                </div><br>
                                <div class="no_modepayement_facultatif">
                                    <div>
                                        <label>Montant avancé<span style="color: red">*</span> </label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-money"></i></span><input type="text" value="0" min="0" name="avance" class="form-control">
                                        </div>
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
                                </div> <br>
                                <br><h2 class="font-bold total text-right total">0 Fcfa</h2>
                                <div>
                                    <div>
                                        <label>Frais de transport<span style="color: red">*</span> </label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-money"></i></span><input type="text" value="0" min="0" name="transport" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="typeapprovisionnement_id" value="<?= Home\TYPEAPPROVISIONNEMENT::EMBALLAGE ?>">
                                <hr/>
                                <button onclick="validerApprovisionnement()" type="button" class="btn btn-success btn-block dim"><i class="fa fa-check"></i> Valider l'approvisionnement</button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>


