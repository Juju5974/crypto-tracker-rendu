const currency = document.getElementById('form_currency');
const quantity = document.getElementById('form_quantity');
const amount = document.getElementById('form_amount');

//currency.addEventListener('change', priceFunction);
quantity.addEventListener('change', priceFunction);
amount.addEventListener('change', quantityFunction);

function priceFunction() {
    amount.value = 'test1';
};

function quantityFunction() {
    quantity.value = 'test2';
};