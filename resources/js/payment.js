const payButton = document.getElementById('pay-now');
if (payButton) {
    payButton.addEventListener('click', async () => {
        const message = document.getElementById('payment-message');
        message.textContent = '';
        payButton.disabled = true;
        try {
            if (!window.snap) throw new Error('Antarmuka Midtrans belum termuat. Muat ulang halaman dan coba kembali.');
            const response = await fetch(payButton.dataset.url, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                credentials: 'same-origin',
            });
            const data = await response.json();
            if (!response.ok) throw new Error(Object.values(data.errors ?? {}).flat()[0] ?? 'Pembayaran belum dapat dibuka. Periksa status kembali.');
            const verify = () => document.getElementById('payment-callback-form').requestSubmit();
            window.snap.pay(data.token, { onSuccess: verify, onPending: verify, onError: verify, onClose: verify });
        } catch (error) {
            message.textContent = error.message || 'Terjadi gangguan. Muat ulang dan periksa status pembayaran.';
            payButton.disabled = false;
        }
    });
}