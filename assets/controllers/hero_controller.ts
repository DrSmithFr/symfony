import { Controller } from '@hotwired/stimulus';
import YouTubePlayer from 'youtube-player';
import { YouTubePlayer as Player } from 'youtube-player/dist/types';
import PlayerStates from 'youtube-player/dist/constants/PlayerStates';

export default class extends Controller {
    static targets: string[]   = ['wrapper', 'video']
    player: Player | undefined = undefined;

    connect() {
        const playerWrapper = this.targets.find('wrapper') as HTMLElement;
        const playerElement = this.targets.find('video') as HTMLElement;

        if (!playerWrapper || !playerElement) {
            throw new Error('YouTubePlayer: Player Wrapper must be defined');
        }

        const videoId = playerElement.dataset.videoId;

        if (videoId === undefined) {
            throw new Error('YouTubePlayer: Video ID must be defined');
        }

        this.player = YouTubePlayer(
            playerElement.id,
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
                    console.log('HeroPlayer should start soon!');
                    this
                        .player
                        ?.playVideo()
                        .then(() => {
                            console.log('HeroPlayer has been started!');
                        })
                }, 100)
            });

        this
            .player
            .on('stateChange', e => {
                switch (e.data) {
                    case PlayerStates.PLAYING: // playing
                        setTimeout(() => {
                            playerWrapper.classList.add('displayed');
                        }, 1000)
                        break;
                    case PlayerStates.ENDED: // ended
                        playerWrapper.classList.remove('displayed');
                        break;
                }
            });
    }

    startVideo() {
        if (!this.player) {
            throw new Error('YouTubePlayer: Player is not defined');
        }

        this
            .player
            .playVideo()
            .then(() => {})
    }
}
