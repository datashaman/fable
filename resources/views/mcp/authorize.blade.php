<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head', ['title' => 'Authorize '.$client->name])
    </head>
    <body class="fable-consent-body">
        <main class="fable-consent-shell">
            <article class="fable-consent" aria-labelledby="authorization-title">
                <header class="fable-consent-header">
                    <div class="fable-consent-brand" aria-label="{{ config('app.name', 'Fable') }}">
                        <span class="fable-consent-mark" aria-hidden="true">
                            <x-app-logo-icon />
                        </span>
                        <span>{{ config('app.name', 'Fable') }}</span>
                    </div>

                    <p class="fable-eyebrow">MCP access</p>
                    <h1 id="authorization-title" class="fable-consent-title">Authorize {{ $client->name }}</h1>
                    <p class="fable-consent-intro">
                        {{ $client->name }} is asking to connect to Fable. Review the account and requested access before continuing.
                    </p>
                </header>

                <dl class="fable-consent-details">
                    <div class="fable-consent-detail">
                        <dt>Account</dt>
                        <dd>{{ $user->email }}</dd>
                    </div>

                    <div class="fable-consent-detail fable-consent-permissions">
                        <dt>Requested access</dt>
                        <dd>
                            @if (count($scopes) > 0)
                                <ul>
                                    @foreach ($scopes as $scope)
                                        <li>
                                            <span class="fable-consent-permission-mark" aria-hidden="true"></span>
                                            <span>{{ $scope->description }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="fable-consent-muted">No additional permissions requested.</span>
                            @endif
                        </dd>
                    </div>
                </dl>

                <p class="fable-consent-advice">Only authorize this connection if you initiated it.</p>

                <footer class="fable-consent-actions">
                    <form method="POST" action="{{ route('passport.authorizations.deny') }}" id="cancelForm">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="state" value="">
                        <input type="hidden" name="client_id" value="{{ $client->id }}">
                        <input type="hidden" name="auth_token" value="{{ $authToken }}">
                        <button type="submit" class="fable-consent-button fable-consent-button-secondary">
                            Cancel
                        </button>
                    </form>

                    <form method="POST" action="{{ route('passport.authorizations.approve') }}" id="authorizeForm">
                        @csrf
                        <input type="hidden" name="state" value="">
                        <input type="hidden" name="client_id" value="{{ $client->id }}">
                        <input type="hidden" name="auth_token" value="{{ $authToken }}">
                        <button type="submit" class="fable-consent-button fable-consent-button-primary" id="authorizeButton">
                            <svg id="loadingSpinner" class="fable-consent-spinner hidden" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" opacity=".3"></circle>
                                <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                            </svg>
                            <span id="authorizeText">Authorize</span>
                        </button>
                    </form>
                </footer>
            </article>
        </main>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const authorizeForm = document.getElementById('authorizeForm');
                const authorizeButton = document.getElementById('authorizeButton');
                const authorizeText = document.getElementById('authorizeText');
                const loadingSpinner = document.getElementById('loadingSpinner');
                const cancelForm = document.getElementById('cancelForm');

                authorizeForm.addEventListener('submit', function () {
                    authorizeButton.disabled = true;
                    authorizeText.textContent = 'Authorizing…';
                    loadingSpinner.classList.remove('hidden');

                    setTimeout(function () {
                        const checkRedirect = setInterval(function () {
                            if (!window.location.href.includes('/oauth/authorize') ||
                                window.location.search.includes('code=') ||
                                window.location.search.includes('error=')) {
                                clearInterval(checkRedirect);
                                window.close();
                            }
                        }, 100);

                        setTimeout(function () {
                            clearInterval(checkRedirect);
                            window.close();
                        }, 5000);
                    }, 200);
                });

                cancelForm.addEventListener('submit', function () {
                    setTimeout(function () {
                        window.close();
                    }, 200);
                });
            });
        </script>
    </body>
</html>
