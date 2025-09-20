// PopoverLayer.ts
export class PopoverLayer {
    private static layer: HTMLElement | null = null
    private static activePopovers: HTMLElement[] = []

    private static ensureLayer(): HTMLElement {
        if (!this.layer) {
            this.layer = document.createElement('div')
            this.layer.id = 'popover-layer'

            Object.assign(this.layer.style, {
                position: 'absolute',
                top: '0',
                bottom: '0',
                left: '0',
                right: '0',
                zIndex: '100',
                pointerEvents: 'none'
            })

            document.body.appendChild(this.layer)
        }
        return this.layer
    }

    static open(popover: HTMLElement): void {
        this.clearAll();

        const layer = this.ensureLayer()

        // pointer events sur le popover mais pas sur la couche
        popover.style.position = 'fixed'
        popover.style.pointerEvents = 'auto'

        layer.appendChild(popover)
        this.activePopovers.push(popover)
    }

    static close(popover: HTMLElement) {
        const index = this.activePopovers.indexOf(popover)
        if (index > -1) {
            this.activePopovers.splice(index, 1)
            popover.remove()
        }
    }

    static clearAll() {
        this.activePopovers.forEach(p => p.remove())
        this.activePopovers = []
    }
}
