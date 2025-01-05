<div class="row">
    <div class="col-12">
        <div class="card" id="card_list_{{  $object->getObjectProperty('objectID') }}">
            <div class="card-header bg-secondary bg-opacity-25  pt-1 pb-1 pe-1 fs--1 ">
                <div class="row flex-between-end">
                    <div class="col-auto align-self-center my-2 ">
                        <span id="formObjectTitle" data-object-id="{{ $object->getObjectProperty('objectID') }}"
                              class="title fs--1">{{  $object->getObjectProperty('objectName')  }} </span>
                    </div>
                    <div class="col-auto ms-auto my-1"
                         id="{{  $object->getObjectProperty('objectID') }}_tableButtonBar">
                        <div class="nav nav-pills fs--1 nav-pills-falcon  " role="tablist">
                            @if($object->getListProperty('writable') === true)
                                {{--                                @if($object->getFormPropertY('dialogHandler') === 'inCard')--}}
                                {{--                                    <button class="btn btn-sm fs--1 active" data-bs-toggle="pill"--}}
                                {{--                                            data-bs-target="#dom-view-{{ $object->getObjectProperty('objectID')  }}"--}}
                                {{--                                            type="button" role="tab"--}}
                                {{--                                            aria-controls="dom-view-{{ $object->getObjectProperty('objectID')  }}"--}}
                                {{--                                            aria-selected="true"--}}
                                {{--                                            id="tab-dom-view-{{ $object->getObjectProperty('objectID')  }}">Anzeigen--}}
                                {{--                                    </button>--}}
                                {{--                                    <button class="btn btn-sm fs--1"--}}
                                {{--                                            data-do-objectID="{{ $object->getObjectProperty('objectID')  }}"--}}
                                {{--                                            data-do-dataset-id="" data-do-type="editEntry" data-bs-toggle="pill"--}}
                                {{--                                            data-bs-target="#dom-edit-{{ $object->getObjectProperty('objectID')  }}"--}}
                                {{--                                            type="button" role="tab"--}}
                                {{--                                            aria-controls="dom-edit-{{ $object->getObjectProperty('objectID')  }}"--}}
                                {{--                                            aria-selected="false"--}}
                                {{--                                            id="tab-dom-edit-{{ $object->getObjectProperty('objectID')  }}">Bearbeiten--}}
                                {{--                                    </button>--}}
                                {{--                                @else--}}
                                {{--                                    <button class="btn btn-sm btn-light fs--1" type="button"--}}
                                {{--                                            id="{{ $object->getObjectProperty('objectID')  }}_editEntry"--}}
                                {{--                                            data-do-objectID="{{ $object->getObjectProperty('objectID')  }}"--}}
                                {{--                                            data-do-dataset-id="" data-do-type="editEntry"><span--}}
                                {{--                                                class="bi bi-pencil-square me-1"></span>Bearbeiten--}}
                                {{--                                    </button>--}}
                                {{--                                @endif--}}
                                @if($functionButtons)
                                    <button class="btn btn-sm btn-falcon-default fs--1" type="button"
                                            data-bs-toggle="modal" data-bs-target="#dialog-modal"><span
                                                class="bi bi-braces me-1"></span>Function
                                    </button>
                                @endif
                            @endif
                            @if($menuItems)
                                <button class="btn btn-pills btn-sm align-content-sm-center btn-falcon-default fs--1 dropdown-toggle"
                                        id="{{ $object->getObjectProperty('objectID')  }}_dropdownMenuButton"
                                        type="button" data-bs-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">Menu
                                </button>
                                <div class="dropdown font-sans-serif d-inline-block mb-2">
                                    <div id="{{ $object->getObjectProperty('objectID')  }}_dropdownMenu"
                                         class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item" href="#">Menu 1</a>
                                        <a class="dropdown-item" href="#">Menu 2</a>
                                        <a class="dropdown-item" href="#">Menu 3</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Menu 4</a>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0 px-0 col-sm-12 fs--1">
                <table width="100%" id="{{ $object->getObjectProperty('objectID') }}"
                       data-objectid="{{ $object->getObjectProperty('objectID')  }}"
                       data-object-filter="{{ $object->getFilter() }}"
                       data-object-configs="{{ $object->getObjectProperty('configRequestURI') }}"
                       class="table data-tables table-hover table-striped table-hover listObject "
                       style="width:100%">
                    <thead>
                    <tr>
                        @foreach($object->getFieldIndex() as $field)
                            <th id="{{$field}}"></th>
                        @endforeach
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        @foreach($object->getFieldIndex() as $field)
                            <th id="{{$field}}"></th>
                        @endforeach
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>


<script>

</script>
