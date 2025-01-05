<div class="btn-group btn-group-sm" role="group" aria-label="...">
    <button class="btn btn-sm btn-falcon-primary  me-1 my-1 fs--1 " type="button" data-bs-toggle="collapse"
            aria-haspopup="true" data-bs-target="#exampleMenu1" aria-expanded="true" aria-controls="exampleMenu1">
        Auswählen
    </button>
    <button class="btn btn-sm btn-falcon-primary my-1 fs--1" type="button" data-bs-toggle="collapse"
            aria-haspopup="true" data-bs-target="#exampleMenu2" aria-expanded="false" aria-controls="exampleMenu2">
        Zusätzliche Elemente
    </button>
</div>
<div class="collapse show" id="exampleMenu1">
    <div class="row mb-3 " style="border: dot-dash">
        <div class="col-sm-6">
            @if(is_array($object) && $object['objectProperties']['objectType'] == 'form')
                <div class="card mb-3">
                    <div class="card-body">
                        <input type="hidden" id="datasetInput_dataObjectID"
                               value="{{$object['objectProperties']['objectID']}}">
                        <div id="datasetInput_Box" class="form-group form-floating mb-3 col-sm-12">
                            <input class="form-control col-6" id="datasetInput_input" name="datasetInput"
                                   placeholder="Datensatz">
                            <label for="datasetInput_input">Datensatz</label>
                            <button class="btn btn-sm btn-falcon-primary ms-1 mt-1 mb-1 fs--1" type="button "
                                    data-bs-toggle="collapse"
                                    onclick="callDataset()">abrufen
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        <div class="col-sm-6">
            <div class="card mb-3">
                <div class="card-header bg-secondary bg-opacity-25 pt-2 pb-2 fs--1 fw-medium">
                    <div class="row">
                        <div class="col-12 align-self-center">
                            <span class="title">DataObject Vorschau</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 form-group-sm">
                            <label class="form-label" for="dataObjectTypeSelector">ObjectType</label>
                            <select class="form-select form-group-sm fs--1" id="dataObjectTypeSelector"
                                    name="dataObjectTypeSelector">
                                <option selected="selected">Auswählen...</option>
                                <option value="list">ListObject (Liste)</option>
                                <option value="form">FormObject (Form)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="dataObjectSelector">ObjectID</label>
                            <select class="form-select fs--1" id="dataObjectSelector" name="dataObjectSelector">
                                <option selected="selected">Auswählen...</option>
                                @foreach($allDataObjects as $dO)
                                    <option
                                        value="{{ $dO['objectProperties']['objectID'] }}">{{ $dO['objectProperties']['objectName'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row flex-between-center">
                        <div class="col-auto"></div>
                        <div class="col-auto text-center"></div>
                        <div class="col-auto justify-content-lg-end ">
                            <button id="buttonLoadObject" class="btn btn-sm btn-primary me-0 mb-0" type="button">Laden
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="collapse" id="exampleMenu2">
    <div class="row mb-3 " style="border: dot-dash">
        <div class="col-sm-6">
            <div class="card mb-3">
                <div class="card-header bg-100 pt-2 pb-2 fs--1 fw-medium">
                    <div class="">
                        <span class="title">Zusätzliche Elemente</span>
                    </div>
                    <div class="row">

                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12 form-group-sm">
                            <button class="btn btn-sm btn-primary ms-1 mt-1 mb-1 fs--1" type="button"
                                    data-bs-toggle="modal" data-bs-target="#dialog-modal">Dialog
                            </button>
                            <button class="btn btn-sm btn-info ms-1 mt-1 mb-1 fs--1" type="button"
                                    data-bs-toggle="modal" data-bs-target="#info-modal">Infomeldung
                            </button>
                            <button class="btn btn-sm btn-success ms-1 mt-1 mb-1 fs--1" type="button"
                                    data-bs-toggle="modal" data-bs-target="#success-modal">Erfolgsmeldung
                            </button>
                            <button class="btn btn-sm btn-warning ms-1 mt-1 mb-1 fs--1" type="button"
                                    data-bs-toggle="modal" data-bs-target="#warning-modal">Warnmeldung
                            </button>
                            <button class="btn btn-sm btn-danger ms-1 mt-1 mb-1 fs--1" type="button"
                                    data-bs-toggle="modal" data-bs-target="#error-modal">Fehlermeldung
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 form-group-sm">
                            <div class="col-sm-12 bg-secondary form-group-sm">
                                <button class="btn btn-sm btn-falcon-primary ms-1 mt-1 mb-1 fs--1" type="button"
                                        data-bs-toggle="modal" data-bs-target="#dialog-modal">Dialog
                                </button>
                                <button class="btn btn-sm btn-falcon-info ms-1 mt-1 mb-1 fs--1" type="button"
                                        data-bs-toggle="modal" data-bs-target="#info-modal">Infomeldung
                                </button>
                                <button class="btn btn-sm btn-falcon-success ms-1 mt-1 mb-1 fs--1" type="button"
                                        data-bs-toggle="modal" data-bs-target="#success-modal">Erfolgsmeldung
                                </button>
                                <button class="btn btn-sm btn-falcon-warning ms-1 mt-1 mb-1 fs--1" type="button"
                                        data-bs-toggle="modal" data-bs-target="#warning-modal">Warnmeldung
                                </button>
                                <button class="btn btn-sm btn-falcon-danger ms-1 mt-1 mb-1 fs--1" type="button"
                                        data-bs-toggle="modal" data-bs-target="#error-modal">Fehlermeldung
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row flex-between-center">
                        <div class="col-auto"></div>
                        <div class="col-auto text-center"></div>
                        <div class="col-auto justify-content-lg-end ">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class=" col-sm-auto mt-4">
    <div class="row">
        <div class="col-3">

        </div>
        <div class="col-12">
            {!! $objectOutput !!}
        </div>
    </div>
</div>
