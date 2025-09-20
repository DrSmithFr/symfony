import { Controller } from '@hotwired/stimulus';
import YouTubePlayer from 'youtube-player';
import PlayerStates from 'youtube-player/dist/constants/PlayerStates';
import { YouTubePlayer as Player } from 'youtube-player/dist/types';

export default class extends Controller {
    static targets: string[] = ['video']

    player: Player | undefined = undefined;

    declare videoTarget: HTMLElement

    connect() {
        if (!this.videoTarget) {
            throw new Error('PopOverTrailer: video target must be defined');
        }

        const videoId = this.videoTarget.dataset.videoId;

        if (videoId === undefined) {
            throw new Error('PopOverTrailer: Video ID must be defined');
        }

        this.player = YouTubePlayer(
            this.videoTarget,
            {
                videoId: videoId,
                height: '100%',
                width: '100%',
                playerVars: {
                    autoplay: 1,
                    color: 'white',
                    controls: 0,
                    disablekb: 1
                },
            }
        );

        this
            .player
            .on('error', e => {
                console.error(e)
            });

        this
            .player
            .on('ready', e => {
                setTimeout(() => {
                    this
                        .player
                        ?.playVideo()
                        .then(() => {
                        })
                }, 100)
            });

        this
            .player
            .on('stateChange', e => {
                switch (e.data) {
                    case PlayerStates.PLAYING: // playing
                        setTimeout(() => {
                            this.videoTarget.classList.add('playing');
                        }, 1000)
                        break;
                    case PlayerStates.ENDED: // ended
                        this.videoTarget.classList.remove('playing');
                        break;
                }
            });
    }

    disconnect() {
        this.player?.destroy();
        super.disconnect();
    }

    startVideo() {
        if (!this.player) {
            throw new Error('PopOverTrailer: Player is not defined');
        }

        this
            .player
            .playVideo()
            .then(() => {})
    }
}
