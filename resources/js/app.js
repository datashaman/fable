import './echo';

const connectionLabels = {
    connected: 'Live',
    connecting: 'Connecting',
    initialized: 'Connecting',
    unavailable: 'Offline',
    failed: 'Offline',
    disconnected: 'Offline',
};

let connectionState = window.Echo.connector.pusher.connection.state;

const renderConnectionState = () => {
    document.documentElement.dataset.fableConnection = connectionState;

    document.querySelectorAll('[data-fable-connection]').forEach((element) => {
        element.dataset.state = connectionState;
        const label = element.querySelector('[data-fable-connection-label]');

        const nextLabel = connectionLabels[connectionState] ?? 'Reconnecting';

        if (label && label.textContent !== nextLabel) {
            label.textContent = nextLabel;
        }
    });
};

window.Echo.connector.pusher.connection.bind('state_change', ({ current }) => {
    connectionState = current;
    renderConnectionState();
});

document.addEventListener('DOMContentLoaded', renderConnectionState);
document.addEventListener('livewire:navigated', renderConnectionState);

new MutationObserver(renderConnectionState).observe(document.body, {
    childList: true,
    subtree: true,
});
