import {computed} from 'vue';
import {usePage} from '@inertiajs/vue3';

export function useCurrencyFormatter() {
    const page = usePage();
    const currency = computed(() =>
        String((page.props.appSettings as {default_currency?: string} | undefined)?.default_currency ?? 'USD'),
    );

    const formatCurrency = (value: number | string | null | undefined, maximumFractionDigits = 0): string => {
        const amount = Number(value ?? 0);

        return new Intl.NumberFormat('fr-CD', {
            style: 'currency',
            currency: currency.value,
            maximumFractionDigits,
        }).format(Number.isFinite(amount) ? amount : 0);
    };

    return {currency, formatCurrency};
}
