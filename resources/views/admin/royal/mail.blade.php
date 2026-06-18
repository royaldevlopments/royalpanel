@extends('layouts.royal', ['navbar' => 'mail', 'sideEditor' => false])

@section('title')
    Royal Mail
@endsection

@section('content')
    <form action="{{ route('admin.royal.mail') }}" method="POST" class="content-box">
        <div class="header">
            <p>Mail settings</p>
            <span class="description-text">Change the mail template settings.</span>
        </div>

        <x-royal.form-wrapper 
            title="Logo Settings" 
            description="Configure the logo settings for your mail templates."
        >
            <x-royal.input-field 
                id="royal:mail_logo" 
                :value="$mail_logo" 
                label="Mail logo"
            />
            <x-royal.switch 
                id="royal:mail_logoFull"
                name="royal:mail_logoFull"
                :value="$mail_logoFull"
                label="Logo only"
            />
        </x-royal.form-wrapper>
        <x-royal.form-wrapper 
            title="Mail Styling" 
            description="Configure the mail styling settings for your mail templates."
        >
            <div class="input-field">
                <label for="mail_color">Mail primary color</label>
                <x-royal.color-input
                    target="mail_color"
                    id="royal:mail_color"
                    :value="$mail_color"
                />
            </div>
            <div class="input-field">
                <label for="mail_backgroundColor">Mail background color</label>
                <x-royal.color-input
                    target="mail_backgroundColor"
                    id="royal:mail_backgroundColor" 
                    :value="$mail_backgroundColor"
                />
            </div>
            <div class="input-field">
                <label for="royal:mail_mode">Mail color mode</label>
                <select
                    id="royal:mail_mode"
                    name="royal:mail_mode"
                >
                    <option value="dark" @if(old('royal:mail_mode', $mail_mode) == 'dark') selected @endif>Dark mode</option>
                    <option value="light" @if(old('royal:mail_mode', $mail_mode) == 'light') selected @endif>Light mode</option>
                </select>
                <span style="font-size:0.8rem">If the background color is light, use a light setting. If the background color is dark, use a dark setting.</span>
            </div>
        </x-royal.form-wrapper>
        <x-royal.form-wrapper 
            title="Utility Links" 
            description="Configure the utility links settings for your mail templates. Leave empty to remove a specific utility link."
        >
            <x-royal.input-field 
                id="royal:mail_status" 
                :value="$mail_status"
                label="Mail status page"
            />
            <x-royal.input-field 
                id="royal:mail_billing" 
                :value="$mail_billing" 
                label="Mail billing"
            />
            <x-royal.input-field 
                id="royal:mail_support" 
                :value="$mail_support" 
                label="Mail support"
            />
        </x-royal.form-wrapper>
        <x-royal.form-wrapper 
            title="Mail socials" 
            description="Configure the mail socials settings for your mail templates. Leave empty to remove a specific social link."
        >
            <x-royal.input-field 
                id="royal:mail_discord" 
                :value="$mail_discord" 
                label="Mail discord"
            />
            <x-royal.input-field 
                id="royal:mail_twitter" 
                :value="$mail_twitter" 
                label="Mail Twitter"
            />
            <x-royal.input-field 
                id="royal:mail_facebook" 
                :value="$mail_facebook" 
                label="Mail Facebook"
            />
            <x-royal.input-field 
                id="royal:mail_instagram" 
                :value="$mail_instagram" 
                label="Mail Instagram"
            />
            <x-royal.input-field 
                id="royal:mail_linkedin" 
                :value="$mail_linkedin" 
                label="Mail Linkedin"
            />
            <x-royal.input-field 
                id="royal:mail_youtube" 
                :value="$mail_youtube" 
                label="Mail Youtube"
            />
        </x-royal.form-wrapper>

        <div class="floating-button">
            {!! csrf_field() !!}
            <button type="submit" class="button button-primary">Save changes</button>
        </div>
    </form>
@endsection