<x-layout title="Pusat Notifikasi">
    <div class="page-header notifications-heading">
        <div>
            <h1 class="page-title">Pusat Notifikasi</h1>
            <p class="page-lead">
                Informasi terbaru seputar kegiatan pembelajaranmu.
            </p>
        </div>

        @if (auth()->user()?->unreadNotifications()->exists())
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="primary-btn notifications-read-all-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <path d="m5 12 4 4L19 6"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    Tandai Semua Sudah Dibaca
                </button>
            </form>
        @endif
    </div>

    <section class="content-card notifications-card">
        <header class="notifications-card-header">
            <div>
                <h2>Semua Notifikasi</h2>
                <p>Periksa informasi dan pembaruan terbaru akunmu.</p>
            </div>

            <span class="notifications-total">
                {{ $notifications->total() }} notifikasi
            </span>
        </header>

        @if (session('success'))
            <div class="notifications-alert notifications-alert-success" role="status">
                {{ session('success') }}
            </div>
        @endif

        <div class="notifications-list">
            @forelse ($notifications as $notification)
                @php
                    $data = $notification->data ?? [];

                    $title = $data['title']
                        ?? $data['assignment_title']
                        ?? 'Notifikasi';

                    $message = $data['message']
                        ?? $data['body']
                        ?? (isset($data['course_name'])
                            ? 'Mata kuliah: ' . $data['course_name']
                            : 'Ada informasi baru untuk kamu.');

                    $url = $data['url'] ?? null;

                    $safeUrl = is_string($url)
                        && str_starts_with($url, '/')
                        && ! str_starts_with($url, '//')
                        ? $url
                        : null;
                @endphp

                <article class="notification-item {{ $notification->read_at ? 'is-read' : 'is-unread' }}">
                    <div class="notification-status-icon {{ $notification->read_at ? 'is-read' : 'is-unread' }}"
                        aria-hidden="true">
                        @if ($notification->read_at)
                            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m5 12 4 4L19 6"/>
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                                <path d="M10 21h4"/>
                            </svg>
                        @endif
                    </div>

                    <div class="notification-item-content">
                        <div class="notification-item-heading">
                            @if ($safeUrl)
                                <a href="{{ $safeUrl }}" class="notification-item-link">
                                    {{ $title }}
                                </a>
                            @else
                                <h3>{{ $title }}</h3>
                            @endif

                            @if (! $notification->read_at)
                                <span class="notification-unread-badge">Baru</span>
                            @endif
                        </div>

                        <p>{{ $message }}</p>

                        <div class="notification-item-meta">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                aria-hidden="true">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            <time datetime="{{ $notification->created_at?->toISOString() }}">
                                {{ $notification->created_at?->diffForHumans() }}
                            </time>
                        </div>
                    </div>

                    @if (! $notification->read_at)
                        <div class="notification-item-actions">
                            <form
                                action="{{ route('notifications.read', $notification->id) }}"
                                method="POST"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="notification-mark-read-btn"
                                    title="Tandai sudah dibaca"
                                    aria-label="Tandai notifikasi {{ $title }} sudah dibaca"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        aria-hidden="true">
                                        <path d="m5 12 4 4L19 6"/>
                                    </svg>
                                    Tandai dibaca
                                </button>
                            </form>
                        </div>
                    @endif
                </article>
            @empty
                <div class="notification-empty">
                    <div class="notification-empty-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                            <path d="M10 21h4"/>
                        </svg>
                    </div>

                    <h3>Belum Ada Notifikasi</h3>
                    <p>
                        Saat ada informasi terbaru seputar kegiatan pembelajaran,
                        notifikasi akan ditampilkan di sini.
                    </p>
                </div>
            @endforelse
        </div>
    </section>

    @if (method_exists($notifications, 'links'))
        <div class="notifications-pagination">
            {{ $notifications->links('vendor.pagination.kampuslms') }}
        </div>
    @endif
</x-layout>