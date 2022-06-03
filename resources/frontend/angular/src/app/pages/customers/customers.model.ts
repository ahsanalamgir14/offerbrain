export class Customer {
    id: number;
    email: string;
    first_name: string;
    last_name: string;
    phone: string;
    addresses: string;
    orders_count: string;
    
    constructor(customer) {
        this.id = customer.id;
        this.email = customer.email;
        this.first_name = customer.first_name;
        this.last_name = customer.last_name;
        this.addresses = customer.addresses;
        this.orders_count = customer.orders_count;
    }
}