import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    static targets: string[] = ['modal'];

    declare readonly hasInputTarget: boolean
    declare readonly inputTarget: HTMLInputElement
    declare readonly inputTargets: HTMLInputElement[]

    connect() {
        console.log('☕️');
    }

    openPasswordReset(event: Event) {
        event.preventDefault();

        const modalElement = this.targets.find('modal');
        if (modalElement === undefined) {
            throw new Error('Modal element not found');
        }

        const modal = new Modal(modalElement);
        modal.show();
    }
}
