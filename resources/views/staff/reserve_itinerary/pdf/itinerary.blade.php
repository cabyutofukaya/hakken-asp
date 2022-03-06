@extends('layouts.staff.pdf')

@section('content')
<div class="documentPrint">
	<h2 class="blockTitle">行程表</h2>
	<div class="number">
		<p>行程表番号：{{ $reserveItinerary->control_number }}</p>
    <p>発行日：{{ $reserveItinerary->created_at->format('Y/m/d') }}</p>
	</div>
	<div class="dcHead">
		<div>
			<p>
				@if($reserveItinerary->reserve->representatives->isNotEmpty())
					{{ $reserveItinerary->reserve->representatives[0]->name ?? '-' }}
					@if($reserveItinerary->reserve->representatives[0]->name_roman)
						({{ $reserveItinerary->reserve->representatives[0]->name_roman }})
					@endif
					様
				@endif
			</p>
			<p class="dispTitle">件名 {{ $reserveItinerary->reserve->name ?? '' }}</p>
			<p class="dispPeriod">期間 {{ $reserveItinerary->reserve->departure_date }}～{{ $reserveItinerary->reserve->return_date }}</p>
		</div>
		<div class="dispCorp">
			<p class="dispCompany">{{ $consts['companyInfo']->company_name ?? '-' }}</p>
			<p class="dispCorpContact">TEL:{{ $consts['companyInfo']->tel ?? '-' }} / FAX:{{ $consts['companyInfo']->fax ?? '-' }}</p>
			<p class="dispManager">担当 {{ $reserveItinerary->reserve->manager->name ?? '' }}</p>
		</div>
	</div>
		
	@foreach($reserveItinerary->reserve_travel_dates as $travelDate)
		<div class="dispCourse @if($travelDate->reserve_schedules->where('type', config('consts.reserve_itineraries.ITINERARY_TYPE_WAYPOINT_IMAGE'))->isEmpty())single @endif">
			<div class="dispPlan">
				<h3>{{ $travelDate->travel_date_jp }}({{ $travelDate->travel_date_week }})</h3>
				<div class="inner">
					<ul class="spotList">
						@foreach($travelDate->reserve_schedules as $schedule)
							<li>
								<div class="spotBlock {{ $schedule->transportation }}">
									<span class="time">
										@if($loop->first){{ $schedule->departure_time }}@else{{ $schedule->arrival_time }}@endif
									</span>
									@if($schedule->type === config('consts.reserve_itineraries.ITINERARY_TYPE_WAYPOINT_IMAGE'))
										<h4 class="pickup">{{ $schedule->place }}
											@if(!$loop->first && $schedule->staying_time)<span>{{ $schedule->staying_time }}滞在</span>@endif{{-- 先頭スケジュールの場合は滞在表記ナシ --}}
										</h4>
										<p>{!! nl2br(e($schedule->explanation ?? " ")) !!}</p>
									@else
										<h4>{{ $schedule->place ?? " " }}</h4>
										<p>{!! nl2br(e($schedule->explanation ?? "")) !!}</p>
									@endif
								</div>
							</li>
						@endforeach
					</ul>
				</div>
			</div>
				
			@if($travelDate->reserve_schedules->where('type', config('consts.reserve_itineraries.ITINERARY_TYPE_WAYPOINT_IMAGE'))->isNotEmpty())
				<div class="dispPh">
					@foreach($travelDate->reserve_schedules->where('type', config('consts.reserve_itineraries.ITINERARY_TYPE_WAYPOINT_IMAGE')) as $schedule)
						<div class="dispSpotPh">
							<div class="ph">
								@if($schedule->reserve_schedule_photos[0]->file_name)
									<img src="{{$consts['thumbMBaseUrl'] . $schedule->reserve_schedule_photos[0]->file_name}}" alt="">
								@endif
							</div>
							<h4>{{ $schedule->place }}</h4>
							<p>{!! nl2br(e($schedule->reserve_schedule_photos[0]->description)) !!}</p>
						</div>
					@endforeach
				</div>
			@endif
		</div>
	@endforeach
</div>
@endsection