<x-layout title="Pusat Notifikasi">
    <section class="notifications-page">
        <div class="notifications-heading">
            <div>
                <h1>Pusat Notifikasi</h1>
                <p>Informasi terbaru seputar kegiatan pembelajaranmu.</p>
            </div>
        </div>

        <div class="notifications-list">
            @forelse ($notifications as $notification)
                <article class="notification-item
                    {{ $notification->read_at ? '' : 'is-unread' }}">

                    <strong>
                        {{ $notification->data['title'] ?? 'Notifikasi' }}
                    </strong>

                    <p>
                        {{ $notification->data['message']
                            ?? $notification->data['body']
                            ?? 'Ada informasi baru untuk kamu.' }}
                    </p>

                    <small>
                        {{ $notification->created_at?->diffForHumans() }}
                    </small>
                </article>
            @empty
                <p class="notification-empty">
                    Belum ada notifikasi untuk ditampilkan.
                </p>
            @endforelse
        </div>

        @if (method_exists($notifications, 'links'))
            {{ $notifications->links() }}
        @endif
    </section>
</x-layout>
