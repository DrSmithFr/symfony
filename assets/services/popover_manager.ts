// PopoverLayer.ts
export class PopoverManager {

    private static LAYER_ID = 'layer-container';
    private static activePopovers: HTMLElement[] = []

    static getLayer(): HTMLElement {
        let layer = document.getElementById(this.LAYER_ID);

        if (!layer) {
            layer = document.createElement('div')
            layer.id = this.LAYER_ID

            Object.assign(layer.style, {
                position: 'absolute',

                top: '0',
                bottom: '0',
                left: '0',
                right: '0',

                zIndex: '100',
                pointerEvents: 'none'
            })

            document.body.appendChild(layer)

            // Allow to expand the node in debuggers
            layer.innerHTML = '<br>'
        }

        return layer
    }

    static open(popover: HTMLElement): void {
        this.clearAll();

        const layer = this.getLayer()

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
