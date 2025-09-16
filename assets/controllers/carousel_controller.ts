import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets: string[] = ['item']

    declare readonly itemTargets: HTMLElement[]

    private activeContainer = 1
    private activeInterval: number | null = null

    private DEFAULT_TIMEOUT = 5000;

    connect() {
        this.startInterval();
    }

    private startInterval() {
        if (this.activeInterval !== null) {
            return
        }

        this.activeInterval = window.setInterval(() => {
            this.clearExpanded()
            this.expandItem(this.activeContainer)

            this.activeContainer += 1
            if (this.activeContainer >= this.itemTargets.length) {
                this.activeContainer = 0
            }
        }, this.DEFAULT_TIMEOUT)
    }

    private stopInterval() {
        if (this.activeInterval !== null) {
            clearInterval(this.activeInterval)
            this.activeInterval = null
        }
    }

    private clearExpanded() {
        this.itemTargets.forEach(el => el.classList.remove('expanded'))
    }

    private expandItem(indexOrEl: number | HTMLElement) {
        if (typeof indexOrEl === 'number') {
            this.itemTargets[indexOrEl].classList.add('expanded')
            this.activeContainer = indexOrEl
        } else {
            indexOrEl.classList.add('expanded');
            this.activeContainer = this.getIndexFromElement(indexOrEl)
        }
    }

    private getIndexFromElement(element: HTMLElement) {
        const index = this.itemTargets.indexOf(element);
        return index === -1 ? 0 : index
    }

    clickItem(event: Event) {
        console.log(event);
        const target = event.currentTarget as HTMLElement
        this.clearExpanded()
        this.expandItem(target)
        this.stopInterval()
    }

    hoverContainer() {
        this.stopInterval()
    }

    leaveContainer() {
        this.startInterval()
    }
}
