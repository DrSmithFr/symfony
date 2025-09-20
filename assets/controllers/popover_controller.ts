import { Controller } from '@hotwired/stimulus';
import { PopoverLayer } from '../services/popover_manager';

export default class extends Controller {
    static targets: string[] = ['content']

    declare contentTarget: HTMLElement
    declare wrapperTarget: HTMLElement

    declare hasContentTarget: boolean

    private displayTimeout: number | undefined      = undefined;
    private popoverElement: HTMLElement | undefined = undefined;

    BEFORE_DISPLAY_TIME    = 300
    CLAMP_SCREEN_MARGIN_PX = 5

    connect() {
        this.wrapperTarget = PopoverLayer.ensureLayer();
    }

    async show(event: MouseEvent): Promise<void> {
        let content: string = this.contentTarget.innerHTML
        if (!content) return

        // adding popover in wrapper
        const fragment: DocumentFragment = document.createRange().createContextualFragment(content)
        const popover: HTMLElement       = fragment.firstElementChild as HTMLElement

        this.wrapperTarget.appendChild(fragment)
        PopoverLayer.open(popover)
        this.popoverElement = popover

        // calculer le centre du trigger
        const rect = (event.target as HTMLElement).getBoundingClientRect()

        let centerX = rect.left + rect.width / 2
        let centerY = rect.top + rect.height / 2

        // taille popover
        const popRect = this.popoverElement.getBoundingClientRect()

        // clamp sur l’écran
        const margin = this.CLAMP_SCREEN_MARGIN_PX
        centerX      = Math.min(
            window.innerWidth - popRect.width / 2 - margin,
            Math.max(popRect.width / 2 + margin, centerX)
        )
        centerY      = Math.min(
            window.innerHeight - popRect.height / 2 - margin,
            Math.max(popRect.height / 2 + margin, centerY)
        )

        // positionner le popover centré sur le trigger
        this.popoverElement.style.position      = 'fixed'
        this.popoverElement.style.left          = `${centerX}px`
        this.popoverElement.style.top           = `${centerY}px`
        this.popoverElement.style.pointerEvents = 'auto';

        // listener pour fermer quand la souris quitte le popover
        this.popoverElement.addEventListener('mouseleave', () => {
            this.hide()
        });
    }

    hide(): void {
        if (this.popoverElement) {
            this.popoverElement.remove();
            this.popoverElement = undefined;
        }
    }

    scheduleShow(e: MouseEvent) {
        if (this.popoverElement) {
            return
        }

        if (this.displayTimeout) {
            clearTimeout(this.displayTimeout)
            this.displayTimeout = undefined
        }

        this.displayTimeout = window.setTimeout(() => {
            this
                .show(e)
                .then(() => {
                    this.displayTimeout = undefined;
                })
        }, this.BEFORE_DISPLAY_TIME)
    }

    cancelShow() {
        if (this.displayTimeout) {
            clearTimeout(this.displayTimeout)
            this.displayTimeout = undefined
        }
    }
}
