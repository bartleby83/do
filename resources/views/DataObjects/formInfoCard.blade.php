<div class="row">
    <div class="col-12">
        <!--begin::Card-->
        <div class="card card-flush">
            <!--begin::Card header-->
            <div class="card-header bg-secondary bg-opacity-25  pt-1 pb-1 pe-1 fs--1 ">
                <div class="row flex-between-end">
                    <div class="col-auto align-self-center my-2 ">
                        <span id="formObjectTitle" data-object-id="{{ $object->getObjectProperty('objectID') }}"
                              class="title fs--1">{{  $object->getObjectProperty('objectName')  }} </span>
                    </div>
                </div>
                <div class="card-toolbar">
                    <div id="{{ $object->getObjectProperty('objectID') }}_formInfoCardButtonBar"
                         class="nav nav-tabs nav-line-tabs pt-1 pb-1 pe-1" role="tablist">
                        @if($object->getFormProperty('writable') === true)
                            @if($object->getFormProperty('dialogHandler') === 'inCard')
                                <button class="fs--1 nav-link active" data-bs-toggle="pill"
                                        data-bs-target="#dom-view-{{ $object->getObjectProperty('objectID')  }}"
                                        type="button" role="tab"
                                        aria-controls="dom-view-{{ $object->getObjectProperty('objectID')  }}"
                                        aria-selected="true"
                                        id="tab-dom-view-{{ $object->getObjectProperty('objectID')  }}">Anzeigen
                                </button>
                                <button class="nav-link fs--1"
                                        data-do-objectID="{{ $object->getObjectProperty('objectID')  }}"
                                        data-do-dataset-id="" data-do-type="editEntry" data-bs-toggle="pill"
                                        data-bs-target="#dom-edit-{{ $object->getObjectProperty('objectID')  }}"
                                        type="button" role="tab"
                                        aria-controls="dom-edit-{{ $object->getObjectProperty('objectID')  }}"
                                        aria-selected="false"
                                        id="tab-dom-edit-{{ $object->getObjectProperty('objectID')  }}">Bearbeiten
                                </button>
                            @else
                                <button class="btn btn-sm btn-light fs--1" type="button"
                                        id="{{ $object->getObjectProperty('objectID')  }}_editEntry"
                                        data-do-objectID="{{ $object->getObjectProperty('objectID')  }}"
                                        data-do-dataset-id="" data-do-type="editEntry"><span
                                            class="bi bi-pencil-square me-1"></span>Bearbeiten
                                </button>
                            @endif
                            @if($functionButtons)
                                <button class="btn btn-sm btn-falcon-default fs--1" type="button" data-bs-toggle="modal"
                                        data-bs-target="#dialog-modal"><span class="bi bi-braces me-1"></span>Function
                                </button>
                            @endif
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
                        {{--                @if(Auth::user()->hasRole('nf-admin'))--}}
                        {{--                    <button class="btn btn-sm align-content-sm-center btn-falcon-default fs--1" id="{{ $object->getObjectProperty('objectID')  }}_formInfoButton" type="button" aria-haspopup="true" aria-expanded="false"><span class="bi bi-info-circle m-1"></span></button>--}}
                        {{--                @endif--}}
                    </div>

                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body p-0 px-0 col-sm-12 fs--1">
                <!--begin:::Tab content-->
                <div class="tab-content">
                    <div class="tab-pane preview-tab-pane active" role="tabpanel"
                         aria-labelledby="tab-dom-view-{{ $object->getObjectProperty('objectID')  }}"
                         id="dom-view-{{ $object->getObjectProperty('objectID')  }}">
                        <div class="card m-0">
                            <div class="card-body position-relative">
                                <div class="row m-0">
                                    @if($object->getFormProperty('viewMode') === 'view')
                                        <form id="{{ $object->getObjectProperty('objectID') }}"
                                              data-objectid="{{ $object->getObjectProperty('objectID')  }}"
                                              data-object-configs="{{ $object->getObjectProperty('configRequestURI') }}"
                                              data-id="{{ $dataSetID }}" data-identifier="" class="row formObject">
                                            @endif
                                            <div id="{{ $object->getObjectProperty('objectID') }}_fieldDataContainer">
                                                @if($object->getFormProperty('grouping') !== false)
                                                    @foreach($fieldGroups as $group)
                                                        <div class="row">
                                                            @foreach($group as $field)
                                                                <div class="col-sm-{{ 12 / count($group) }} fs--1 fs--2"
                                                                     id="viewEntryBox_{{ $object->getObjectProperty('objectID')  }}_{{ $field }}"
                                                                     style="border:0px solid"><span
                                                                            class="placeholder w-100"></span></div>
                                                            @endforeach
                                                        </div>
                                                    @endforeach
                                                @endif
                                                @foreach($fieldOutput as $field)
                                                    <div class="row">
                                                        <div class="col-sm-12 fs--1"
                                                             id="viewEntryBox_{{ $object->getObjectProperty('objectID')  }}_{{ $field }}"
                                                             style="border:0px solid"><span
                                                                    class="placeholder w-100"></span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="card-footer">
                                                <div class="row flex-between-center">
                                                    <div
                                                            id="{{ $object->getObjectProperty('objectID') }}_buttonContainerLeft_view"
                                                            class="col-auto"></div>
                                                    <div
                                                            id="{{ $object->getObjectProperty('objectID') }}_buttonContainerCenter_view"
                                                            class="col-auto text-center"></div>
                                                    <div
                                                            id="{{ $object->getObjectProperty('objectID') }}_buttonContainerRight_view"
                                                            class="col-auto justify-content-lg-end "></div>
                                                </div>
                                            </div>
                                            @if($object->getFormProperty('viewMode') === 'view')
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane code-tab-pane" role="tabpanel"
                         aria-labelledby="tab-dom-edit-{{ $object->getObjectProperty('objectID')  }}"
                         id="dom-edit-{{ $object->getObjectProperty('objectID')  }}">
                        <div class="card">
                            @if($object->getFormProperty('viewMode') === 'edit')

                                <form id="{{ $object->getObjectProperty('objectID') }}"
                                      data-objectid="{{ $object->getObjectProperty('objectID')  }}"
                                      data-object-configs="{{ $object->getObjectProperty('configRequestURI') }}"
                                      data-id="{{ $object['dataSetID'] }}" data-identifier=""
                                      class="row mx-2 formObject">
                                    @endif
                                    <div class="card-body position-relative">
                                        <div class="m-0" id="formOutput_{{$object->getObjectProperty('objectID')}}">
                                        </div>
                                    </div>
                                    <div class="card-footer position-relative  ">
                                        <div class="row flex-between-center ">
                                            <div id="{{ $object->getObjectProperty('objectID') }}_buttonContainerLeft"
                                                 class="col-auto"></div>
                                            <div id="{{ $object->getObjectProperty('objectID') }}_buttonContainerCenter"
                                                 class="col-auto text-center"></div>
                                            <div id="{{ $object->getObjectProperty('objectID') }}_buttonContainerRight"
                                                 class="col-auto justify-content-lg-end "></div>
                                        </div>
                                    </div>
                                    @if($object->getFormProperty('viewMode') === 'edit')

                                </form>
                            @endif
                        </div>
                    </div>
                </div>
                <!--end:::Tab content-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
</div>

@php
    $enabled = false;
@endphp

@if($enabled)
    <div class="card mb-3" id="card_form_info_{{ $object->getObjectProperty('objectID') }}">
        <div class="card-header pt-1 pb-1 pe-1 fs--1 fw-medium">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center ms-2 my-2 ">
                <span id="formObjectTitle" data-object-id="{{ $object->getObjectProperty('objectID') }}"
                      class="title">{{ $object->getObjectProperty('objectName')  }}</span>
                </div>
                <div class="col-auto ms-auto my-1 me-2">
                    <div id="{{ $object->getObjectProperty('objectID') }}_formInfoCardButtonBar"
                         class="nav nav-pills fs--1 nav-pills-falcon  " role="tablist">
                        @if($object->getFormProperty('writable') === true)
                            @if($object->getFormProperty('dialogHandler') === 'inCard')
                                <button class="btn btn-sm fs--1 active" data-bs-toggle="pill"
                                        data-bs-target="#dom-view-{{ $object->getObjectProperty('objectID')  }}"
                                        type="button" role="tab"
                                        aria-controls="dom-view-{{ $object->getObjectProperty('objectID')  }}"
                                        aria-selected="true"
                                        id="tab-dom-view-{{ $object->getObjectProperty('objectID')  }}">Anzeigen
                                </button>
                                <button class="btn btn-sm fs--1"
                                        data-do-objectID="{{ $object->getObjectProperty('objectID')  }}"
                                        data-do-dataset-id="" data-do-type="editEntry" data-bs-toggle="pill"
                                        data-bs-target="#dom-edit-{{ $object->getObjectProperty('objectID')  }}"
                                        type="button" role="tab"
                                        aria-controls="dom-edit-{{ $object->getObjectProperty('objectID')  }}"
                                        aria-selected="false"
                                        id="tab-dom-edit-{{ $object->getObjectProperty('objectID')  }}">Bearbeiten
                                </button>
                            @else
                                <button class="btn btn-sm btn-light fs--1" type="button"
                                        id="{{ $object->getObjectProperty('objectID')  }}_editEntry"
                                        data-do-objectID="{{ $object->getObjectProperty('objectID')  }}"
                                        data-do-dataset-id="" data-do-type="editEntry"><span
                                            class="bi bi-pencil-square me-1"></span>Bearbeiten
                                </button>
                            @endif
                            @if($functionButtons)
                                <button class="btn btn-sm btn-falcon-default fs--1" type="button" data-bs-toggle="modal"
                                        data-bs-target="#dialog-modal"><span class="bi bi-braces me-1"></span>Function
                                </button>
                            @endif
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
                        {{--                                        @if(Auth::user()->hasRole('nf-admin'))--}}
                        {{--                                            <button class="btn btn-sm align-content-sm-center btn-falcon-default fs--1" id="{{ $object->getObjectProperty('objectID')  }}_formInfoButton" type="button" aria-haspopup="true" aria-expanded="false"><span class="bi bi-info-circle m-1"></span></button>--}}
                        {{--                                        @endif--}}
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-content">
            <div class="tab-pane preview-tab-pane active" role="tabpanel"
                 aria-labelledby="tab-dom-view-{{ $object->getObjectProperty('objectID')  }}"
                 id="dom-view-{{ $object->getObjectProperty('objectID')  }}">
                <div class="card m-0">
                    <div class="card-body position-relative">
                        <div class="row m-0">
                            @if($object->getFormProperty('viewMode') === 'view')
                                <form id="{{ $object->getObjectProperty('objectID') }}"
                                      data-objectid="{{ $object->getObjectProperty('objectID')  }}"
                                      data-object-configs="{{ $object->getObjectProperty('configRequestURI') }}"
                                      data-id="{{ $dataSetID }}" data-identifier="" class="row formObject">
                                    @endif
                                    <div id="{{ $object->getObjectProperty('objectID') }}_fieldDataContainer">
                                        @if($object->getFormProperty('grouping') !== false)
                                            @foreach($fieldGroups as $group)
                                                <div class="row">
                                                    @foreach($group as $field)
                                                        <div class="col-sm-{{ 12 / count($group) }} fs--1 fs--2"
                                                             id="viewEntryBox_{{ $object->getObjectProperty('objectID')  }}_{{ $field }}"
                                                             style="border:0px solid"><span
                                                                    class="placeholder w-100"></span></div>
                                                    @endforeach
                                                </div>
                                            @endforeach
                                        @endif
                                        @foreach($fieldOutput as $field)
                                            <div class="row">
                                                <div class="col-sm-12 fs--1"
                                                     id="viewEntryBox_{{ $object->getObjectProperty('objectID')  }}_{{ $field }}"
                                                     style="border:0px solid"><span class="placeholder w-100"></span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="card-footer">
                                        <div class="row flex-between-center">
                                            <div
                                                    id="{{ $object->getObjectProperty('objectID') }}_buttonContainerLeft_view"
                                                    class="col-auto"></div>
                                            <div
                                                    id="{{ $object->getObjectProperty('objectID') }}_buttonContainerCenter_view"
                                                    class="col-auto text-center"></div>
                                            <div
                                                    id="{{ $object->getObjectProperty('objectID') }}_buttonContainerRight_view"
                                                    class="col-auto justify-content-lg-end "></div>
                                        </div>
                                    </div>
                                    @if($object->getFormProperty('viewMode') === 'view')
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane code-tab-pane" role="tabpanel"
                 aria-labelledby="tab-dom-edit-{{ $object->getObjectProperty('objectID')  }}"
                 id="dom-edit-{{ $object->getObjectProperty('objectID')  }}">
                <div class="card">
                    @if($object->getFormProperty('viewMode') === 'edit')

                        <form id="{{ $object->getObjectProperty('objectID') }}"
                              data-objectid="{{ $object->getObjectProperty('objectID')  }}"
                              data-object-configs="{{ $object->getObjectProperty('configRequestURI') }}"
                              data-id="{{ $object['dataSetID'] }}" data-identifier="" class="row mx-2 formObject">
                            @endif
                            <div class="card-body position-relative">
                                <div class="m-0" id="formOutput_{{$object->getObjectProperty('objectID')}}">
                                </div>
                            </div>
                            <div class="card-footer position-relative  ">
                                <div class="row flex-between-center ">
                                    <div id="{{ $object->getObjectProperty('objectID') }}_buttonContainerLeft"
                                         class="col-auto"></div>
                                    <div id="{{ $object->getObjectProperty('objectID') }}_buttonContainerCenter"
                                         class="col-auto text-center"></div>
                                    <div id="{{ $object->getObjectProperty('objectID') }}_buttonContainerRight"
                                         class="col-auto justify-content-lg-end "></div>
                                </div>
                            </div>
                            @if($object->getFormProperty('viewMode') === 'edit')

                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
