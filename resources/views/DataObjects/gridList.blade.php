<div class="card my-3">
	<div class="card-header bg-secondary bg-opacity-25 panel-header pt-2 pb-2 fs--1 fw-medium">
		<div class="row flex-between-end">
			<div class="col-auto align-self-center">
				<span id="gridListObjectTitle" data-object-id="{{ $object['objectProperties']['objectID'] }}" class="title">{{ $object['objectProperties']['objectName']  }}</span>
			</div>
			<div class="col-auto ms-auto">
				<div class="nav nav-pills  fs--1 nav-pills-falcon " role="tablist">

{{--					<button class="btn btn-sm fs--1 active" data-bs-toggle="pill" data-bs-target="#dom-1d0504b0-7b41-49d3-967c-f9b6b3006219" type="button" role="tab" aria-controls="dom-1d0504b0-7b41-49d3-967c-f9b6b3006219" aria-selected="true" id="tab-dom-1d0504b0-7b41-49d3-967c-f9b6b3006219">Anzeigen</button>--}}
{{--					<button class="btn btn-sm fs--1" id="{{ $object['objectProperties']['objectID']  }}_editEntry" data-do-objectID="{{ $object['objectProperties']['objectID']  }}" data-do-dataset-id="" data-do-type="editEntry" data-bs-toggle="pill" data-bs-target="#dom-a1310228-43c8-4a2b-90c0-e83d8460c610" type="button" role="tab" aria-controls="dom-a1310228-43c8-4a2b-90c0-e83d8460c610" aria-selected="false" id="tab-dom-a1310228-43c8-4a2b-90c0-e83d8460c610">Bearbeiten</button>--}}

{{--					@if($functionButtons)--}}
{{--						<button class="btn btn-sm btn-falcon-default  fs--1" type="button" data-bs-toggle="modal" data-bs-target="#dialog-modal"><span class="bi bi-braces me-1"></span>Function</button>--}}
{{--					@endif--}}
{{--					@if($menuItems)--}}
{{--						<button class="btn btn-sm align-content-sm-center btn-falcon-default fs--1 dropdown-toggle" id="dropdownMenuButton" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Menu</button>--}}
{{--						<div class="dropdown font-sans-serif d-inline-block mb-2">--}}
{{--							<div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">--}}
{{--								<a class="dropdown-item" href="#">Menu 1</a>--}}
{{--								<a class="dropdown-item" href="#">Menu 2</a>--}}
{{--								<a class="dropdown-item" href="#">Menu 3</a>--}}
{{--								<div class="dropdown-divider"></div>--}}
{{--								<a class="dropdown-item" href="#">Menu 4</a>--}}
{{--							</div>--}}
{{--						</div>--}}
{{--					@endif--}}
				</div>
			</div>
		</div>
	</div>
	<div class="card-body">
		<div class="row gridListObject" id="{{ $object['objectProperties']['objectID'] }}" data-object-id="{{ $object['objectProperties']['objectID'] }}">
			<div class="mb-4 col-md-6 col-lg-4">
				<div class="border rounded-1 h-100 d-flex flex-column justify-content-between pb-3">
					<div class="overflow-hidden">
						<div class="position-relative rounded-top overflow-hidden"><a class="d-block" href="#"><img class="img-fluid rounded-top" src="#" alt="" /></a><span class="badge rounded-pill bg-success position-absolute mt-1 me-2 z-2 top-0 end-0">New</span>
						</div>
						<div class="p-3">
							<h5 class="fs-0"><a class="text-dark" href="#">Partner Name</a></h5>
							<p class="fs--1 mb-3"><a class="text-500" href="#!">Weitere Beschreibung</a></p>

							<p class="fs--1 mb-1">Platz für Text</p>
							<p class="fs--1 mb-1">Noch mehr Platz für Text
							</p>
						</div>
					</div>
					<div class="d-flex flex-between-center px-3">
						<div>Weiterer Text
						</div>
					</div>
				</div>
			</div>
			<div class="mb-4 col-md-6 col-lg-4">
				<div class="border rounded-1 h-100 d-flex flex-column justify-content-between pb-3">
					<div class="overflow-hidden">
						<div class="position-relative rounded-top overflow-hidden"><a class="d-block" href="#"><img class="img-fluid rounded-top" src="#" alt="" /></a><span class="badge rounded-pill bg-success position-absolute mt-1 me-2 z-2 top-0 end-0">New</span>
						</div>
						<div class="p-3">
							<h5 class="fs-0"><a class="text-dark" href="#">Partner Name</a></h5>
							<p class="fs--1 mb-3"><a class="text-500" href="#!">Weitere Beschreibung</a></p>

							<p class="fs--1 mb-1">Platz für Text</p>
							<p class="fs--1 mb-1">Noch mehr Platz für Text
							</p>
						</div>
					</div>
					<div class="d-flex flex-between-center px-3">
						<div>Weiterer Text
						</div>
					</div>
				</div>
			</div>
			<div class="mb-4 col-md-6 col-lg-4">
				<div class="border rounded-1 h-100 d-flex flex-column justify-content-between pb-3">
					<div class="overflow-hidden">
						<div class="position-relative rounded-top overflow-hidden"><a class="d-block" href="#"><img class="img-fluid rounded-top" src="#" alt="" /></a><span class="badge rounded-pill bg-success position-absolute mt-1 me-2 z-2 top-0 end-0">New</span>
						</div>
						<div class="p-3">
							<h5 class="fs-0"><a class="text-dark" href="#">Partner Name</a></h5>
							<p class="fs--1 mb-3"><a class="text-500" href="#!">Weitere Beschreibung</a></p>

							<p class="fs--1 mb-1">Platz für Text</p>
							<p class="fs--1 mb-1">Noch mehr Platz für Text
							</p>
						</div>
					</div>
					<div class="d-flex flex-between-center px-3">
						<div>Weiterer Text
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>
