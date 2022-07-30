export class Customer {
    id: number;
    customer_id: number;
    orders_count: number;
    email: string;
    first_name: string;
    last_name: string;
    phone: string;
    ip_address: string;

    constructor(customer) {
        this.id = customer.id;
        this.customer_id = customer.customer_id;
        this.orders_count = customer.orders_count;
        this.email = customer.email;
        this.first_name = customer.first_name;
        this.last_name = customer.last_name;
        this.phone = customer.phone;
        this.ip_address = customer.ip_address;
    }
}