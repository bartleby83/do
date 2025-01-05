<div class="card mb-3" id="card_form_edit_{{ $object->getObjectProperty('objectID') }}">
    <div class="card-header bg-secondary bg-opacity-25  pt-1 pb-1 pe-1  fs--1 fw-medium">
        <div class="row flex-between-end" style="min-height: 32px">
            <div class="col-auto align-self-center my-2 ">
                <span id="formObjectTitle" data-object-id="{{ $object->getObjectProperty('objectID') }}"
                      class="title">{{ $object->getObjectProperty('objectName')  }}</span>
            </div>
            <div class="col-auto ms-auto">
                <div id="{{ $object->getObjectProperty('objectID') }}_formEditCardButtonBar"
                     class="nav nav-pills  fs--1 nav-pills-falcon " role="tablist">
                    @if($functionButtons)
                        <button class="btn btn-sm btn-falcon-default  fs--1" type="button" data-bs-toggle="modal"
                                data-bs-target="#dialog-modal"><span class="bi bi-braces me-1"></span>Function
                        </button>
                    @endif
                    @if($menuItems)
                        <button class="btn btn-sm align-content-sm-center btn-falcon-default fs--1 dropdown-toggle"
                                id="dropdownMenuButton" type="button" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">Menu
                        </button>
                        <div class="dropdown font-sans-serif d-inline-block mb-2">
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item" href="#">Menu 1</a>
                                <a class="dropdown-item" href="#">Menu 2</a>
                                <a class="dropdown-item" href="#">Menu 3</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">Menu 4</a>
                            </div>
                        </div>
                    @endif
                    @if(Auth::user()->hasRole('grAdmin'))
                        <button class="btn btn-sm align-content-sm-center btn-falcon-default fs--1"
                                id="{{ $object->getObjectProperty('objectID')  }}_formInfoButton" type="button"
                                aria-haspopup="true" aria-expanded="false"><span class="bi bi-info-circle m-1"></span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <form id="{{ $object->getObjectProperty('objectID') }}"
          data-objectid="{{ $object->getObjectProperty('objectID')  }}"
          data-object-configs="{{ $object->getObjectProperty('configRequestURI') }}" data-id="{{ $dataSetID }}"
          data-identifier="" class="row formObject needs-validation" novalidate="" style="width:100%;">
        <div class="card-body">
            <div class="ms-3" id="formOutput_{{$object->getObjectProperty('objectID')}}">
            </div>
        </div>
        <div class="card-footer pt-1 pb-1 pe-1  m-0 fs--1 fw-medium">
            <div class="row flex-between-center">
                <div id="{{ $object->getObjectProperty('objectID') }}_buttonContainerLeft" class="col-auto"></div>
                <div id="{{ $object->getObjectProperty('objectID') }}_buttonContainerCenter"
                     class="col-auto text-center"></div>
                <div id="{{ $object->getObjectProperty('objectID') }}_buttonContainerRight"
                     class="col-auto justify-content-lg-end "></div>
            </div>
        </div>
    </form>
</div>
