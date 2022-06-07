export class Customer {
    id: number;
    orders_count: number;
    email: string;
    first_name: string;
    last_name: string;
    phone: string;
    addresses: string;
    
    constructor(customer) {
        this.id = customer.id;
        this.orders_count = customer.orders_count;
        this.email = customer.email;
        this.first_name = customer.first_name;
        this.last_name = customer.last_name;
        this.phone = customer.phone;
        this.addresses = customer.addresses;
    }
}