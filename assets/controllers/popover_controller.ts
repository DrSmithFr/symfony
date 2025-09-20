import { Controller } from '@hotwired/stimulus';
import { PopoverManager } from '../services/popover_manager';

export default class extends Controller {
    static targets: string[] = ['content']

    declare contentTarget: HTMLElement

    private displayTimeout: number | undefined      = undefined;
    private popoverElement: HTMLElement | undefined = undefined;

    BEFORE_DISPLAY_TIME    = 300
    CLAMP_SCREEN_MARGIN_PX = 5

    connect() {
    }

    async show(event: MouseEvent): Promise<void> {
        let content: string = this.contentTarget.innerHTML
        if (!content) return

        // creating popover element for template
        const fragment: DocumentFragment = document.createRange().createContextualFragment(content)
        const popover: HTMLElement       = fragment.firstElementChild as HTMLElement

        // inject popover in DOM
        PopoverManager.getLayer().appendChild(fragment)
        PopoverManager.open(popover)
        this.popoverElement = popover

        popover.classList.add('active')


        // calculer le centre du trigger
        const position = this.computerDisplayPosition(event.target as HTMLElement, popover)
        popover.style.position      = 'fixed'
        popover.style.left          = `${position.X}px`
        popover.style.top           = `${position.Y}px`

        // restoring pointerEvents
        popover.style.pointerEvents = 'auto';

        // listener pour fermer quand la souris quitte le popover
        popover.addEventListener('mouseleave', () => {
            this.hide()
        });

        // avoid adding popover to page cache (symfony/turbo-ux)
        document.addEventListener('turbo:before-cache', () => {
            PopoverManager.clearAll()
        })
    }

    computerDisplayPosition(target: HTMLElement, popover: HTMLElement): {X: number, Y: number}  {
        const rect = target.getBoundingClientRect()

        let centerX = rect.left + rect.width / 2
        let centerY = rect.top + rect.height / 2

        // taille popover
        const popRect = popover.getBoundingClientRect()

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

        return {
            X : centerX,
            Y: centerY
        }
    }

    hide(): void {
        if (this.popoverElement) {
            PopoverManager.close(this.popoverElement);
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
