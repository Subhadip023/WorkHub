@extends('layouts.admin')

@section('title', 'Profile Settings')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Profile Settings</h1>
</div>

<div class="row">
    <!-- Left Column: Edit Profile & Password -->
    <div class="col-lg-8 mb-4">
        <!-- Profile Information Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Profile Information</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-4">
                    Update your account's profile information and email address.
                </p>

                @if (session('status') === 'profile-updated')
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-1"></i> Profile information updated successfully.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('patch')
                    <input type="hidden" name="cropped_image" id="cropped_image">

                    <div class="d-flex align-items-center mb-4">
                        <div class="position-relative" id="avatarPreviewContainer">
                            @if($user->profile_image)
                                <img id="avatarPreview" src="{{ asset('storage/' . $user->profile_image) }}" alt="Profile" class="rounded-circle img-thumbnail shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                            @else
                                <div id="avatarInitials" class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center font-weight-bold shadow-sm" style="width: 100px; height: 100px; font-size: 2.5rem;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="ml-4">
                            <label for="profile_image" class="text-xs font-weight-bold text-gray-600 uppercase d-block">Profile Image</label>
                            <input type="file" name="profile_image" id="profile_image" class="form-control-file @error('profile_image') is-invalid @enderror">
                            <small class="text-muted d-block mt-1">Select an image to crop and set as your profile photo.</small>
                            @error('profile_image')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="name" class="text-xs font-weight-bold text-gray-600 uppercase">Name</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autocomplete="name">
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label for="email" class="text-xs font-weight-bold text-gray-600 uppercase">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="email">
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div class="mt-3">
                                <p class="text-sm text-warning font-weight-bold">
                                    Your email address is unverified.
                                </p>
                                <button type="submit" form="send-verification" class="btn btn-outline-secondary btn-sm">
                                    Click here to re-send the verification email.
                                </button>
                                
                                @if (session('status') === 'verification-link-sent')
                                    <p class="mt-2 text-success font-weight-bold small">
                                        A new verification link has been sent to your email address.
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </form>

                <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                    @csrf
                </form>
            </div>
        </div>

        <!-- Update Password Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Update Password</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-4">
                    Ensure your account is using a long, random password to stay secure.
                </p>

                @if (session('status') === 'password-updated')
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-1"></i> Password updated successfully.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <form method="post" action="{{ route('password.update') }}">
                    @csrf
                    @method('put')

                    <div class="form-group mb-3">
                        <label for="current_password" class="text-xs font-weight-bold text-gray-600 uppercase">Current Password</label>
                        <input type="password" name="current_password" id="current_password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password">
                        @error('current_password', 'updatePassword')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="password" class="text-xs font-weight-bold text-gray-600 uppercase">New Password</label>
                        <input type="password" name="password" id="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
                        @error('password', 'updatePassword')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label for="password_confirmation" class="text-xs font-weight-bold text-gray-600 uppercase">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
                        @error('password_confirmation', 'updatePassword')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key mr-1"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Delete Account -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow mb-4 border-left-danger">
            <div class="card-header py-3 bg-danger-subtle">
                <h6 class="m-0 font-weight-bold text-danger">Danger Zone</h6>
            </div>
            <div class="card-body">
                <h5 class="h6 font-weight-bold text-gray-900 mb-2">Delete Account</h5>
                <p class="text-muted small mb-4">
                    Once your account is deleted, all of its resources and data will be permanently deleted. This action is irreversible.
                </p>
                <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#confirmDeleteModal">
                    <i class="fas fa-trash-alt mr-1"></i> Delete Account
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')

                <div class="modal-header">
                    <h5 class="modal-title text-danger font-weight-bold" id="confirmDeleteModalLabel">Delete Account</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-gray-800">
                        Are you sure you want to delete your account?
                    </p>
                    <p class="text-muted small">
                        Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.
                    </p>
                    <div class="form-group mt-3">
                        <label for="delete_password" class="text-xs font-weight-bold text-gray-600 uppercase">Password</label>
                        <input type="password" name="password" id="delete_password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" placeholder="Enter password to confirm" required>
                        @error('password', 'userDeletion')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt mr-1"></i> Permanently Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<style>
    .img-container img {
        max-width: 100%;
    }
</style>
@endpush

<!-- Cropper Modal -->
<div class="modal fade" id="cropperModal" tabindex="-1" role="dialog" aria-labelledby="cropperModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-primary font-weight-bold" id="cropperModalLabel">Crop Profile Image</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="img-container">
                    <img id="cropperImage" src="" style="max-height: 450px; max-width: 100%; display: block;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="cropButton">
                    <i class="fas fa-crop-alt mr-1"></i> Crop & Apply
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    $(document).ready(function() {
        @if($errors->userDeletion->isNotEmpty())
            $('#confirmDeleteModal').modal('show');
        @endif

        var $modal = $('#cropperModal');
        var image = document.getElementById('cropperImage');
        var cropper;
        
        $('#profile_image').change(function(e) {
            var files = e.target.files;
            var done = function(url) {
                image.src = url;
                $modal.modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $modal.modal('show');
            };
            var reader;
            var file;
            var url;
            
            if (files && files.length > 0) {
                file = files[0];
                
                if (URL) {
                    done(URL.createObjectURL(file));
                } else if (FileReader) {
                    reader = new FileReader();
                    reader.onload = function(e) {
                        done(reader.result);
                    };
                    reader.readAsDataURL(file);
                }
            }
        });

        $modal.on('shown.bs.modal', function() {
            cropper = new Cropper(image, {
                aspectRatio: 1,
                viewMode: 1,
                autoCropArea: 1,
                responsive: true,
                restore: false,
                checkCrossOrigin: false,
                checkOrientation: false
            });
        }).on('hidden.bs.modal', function() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            document.getElementById('profile_image').value = '';
        });

        $('#cropButton').click(function() {
            var canvas = cropper.getCroppedCanvas({
                width: 300,
                height: 300
            });
            
            if (canvas) {
                var dataUrl = canvas.toDataURL();
                
                var previewImg = $('#avatarPreview');
                if (previewImg.length) {
                    previewImg.attr('src', dataUrl);
                } else {
                    var previewContainer = $('#avatarPreviewContainer');
                    previewContainer.empty().append(
                        $('<img id="avatarPreview" class="rounded-circle img-thumbnail shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">').attr('src', dataUrl)
                    );
                }
                
                $('#cropped_image').val(dataUrl);
                $modal.modal('hide');
            }
        });
    });
</script>
@endpush
