/**
 * External Dependencies
 */
import { wizard } from '@advancedAds/i18n';

/**
 * Internal Dependencies
 */
import StepFooter from '../StepFooter';

export default function BannerAd({ options, setOptions }) {
	let fileFrame = null;

	const handleUpload = (event) => {
		event.preventDefault();

		if (fileFrame) {
			fileFrame.uploader.uploader.param('post_id', 0);
			fileFrame.open();
			return;
		}

		fileFrame = wp.media.frames.file_frame = wp.media({
			title: wizard.bannerAd.mediaFrameTitle,
			button: {
				text: wizard.bannerAd.mediaFrameButton,
			},
			multiple: false,
		});

		fileFrame.on('select', () => {
			const attachment = fileFrame
				.state()
				.get('selection')
				.first()
				.toJSON();

			setOptions('adImage', attachment);
		});

		fileFrame.open();
	};

	return (
		<>
			{options.adImage ? (
				<div className="space-y-4">
					<div>
						<label htmlFor="ad_image_banner">
							<img
								src={options.adImage.url}
								alt="ad_image_banner"
								className="h-80"
							/>
							<br />
							<button
								className="button button-hero button-primary"
								onClick={handleUpload}
							>
								&oplus; {wizard.bannerAd.mediaBtnReplace}
							</button>
						</label>
					</div>
					<div>
						<h2>{wizard.bannerAd.stepHeading}</h2>
						<input
							type="url"
							name="ad_image_url"
							id="ad_image_url"
							className="advads-input-text"
							placeholder={wizard.bannerAd.inputPlaceholder}
							onChange={(event) =>
								setOptions('adImageUrl', event.target.value)
							}
						/>
					</div>
				</div>
			) : (
				<button
					className="button button-hero button-primary"
					onClick={handleUpload}
				>
					&oplus; {wizard.bannerAd.mediaBtnUpload}
				</button>
			)}
			<StepFooter
				isEnabled={options.adImage}
				enableText={wizard.bannerAd.footerEnableText}
				disableText={wizard.bannerAd.footerDisableText}
				onNext={() => {}}
			/>
		</>
	);
}
