<div class="card h-100 shadow-sm border-0 note-card-hover text-center" style="border-radius: 8px;">
    <div class="card-body p-4 d-flex flex-column align-items-center">
        <!-- Avatar -->
        <div class="mb-3">
            @if($user->profile_image)
                <img class="rounded-circle shadow-sm" style="width: 64px; height: 64px; object-fit: cover;" src="{{ asset('storage/' . $user->profile_image) }}">
            @else
                <div class="rounded-circle bg-gray-200 text-gray-600 d-flex align-items-center justify-content-center font-weight-bold shadow-sm" style="width: 64px; height: 64px; font-size: 1.5rem;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
        </div>

        <h6 class="font-weight-bold text-gray-900 mb-1" style="font-size: 1rem;">
            {{ $user->name }}
        </h6>
        
        <p class="text-xs text-muted text-truncate w-100 mb-2">
            <i class="fas fa-envelope mr-1"></i>{{ $user->email }}
        </p>

        <div class="mt-2 w-100">
            @foreach($user->companies as $cu)
                @if($cu->company)
                    <span class="badge badge-light border text-gray-700 px-2 py-1 mr-1 mb-1 font-weight-bold" style="font-size: 0.65rem;">
                        {{ $cu->company->name }}
                    </span>
                @endif
            @endforeach
        </div>
    </div>
</div>
