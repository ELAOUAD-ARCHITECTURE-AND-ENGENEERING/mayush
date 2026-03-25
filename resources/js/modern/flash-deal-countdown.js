export default (endTime) => ({
    days: '00',
    hours: '00',
    minutes: '00',
    seconds: '00',
    expired: false,
    endTime: parseInt(endTime) * 1000,

    init() {
        if (!this.endTime) return;
        this.startCountdown();
    },

    startCountdown() {
        const update = () => {
            const now = new Date().getTime();
            const distance = this.endTime - now;

            if (distance < 0) {
                this.expired = true;
                this.days = '00';
                this.hours = '00';
                this.minutes = '00';
                this.seconds = '00';
                return;
            }

            this.days = String(Math.floor(distance / (1000 * 60 * 60 * 24))).padStart(2, '0');
            this.hours = String(Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
            this.minutes = String(Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
            this.seconds = String(Math.floor((distance % (1000 * 60)) / 1000)).padStart(2, '0');

            requestAnimationFrame(update);
        };
        update();
    }
});
