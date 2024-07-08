/**
 * External Dependencies
 */
import { wizard } from '@advancedAds/i18n';

export default function Newsletter() {
	return (
		<>
			<h1 className="!mt-0">{wizard.newsletter.title}</h1>
			<div className="flex gap-x-4">
				<input
					type="email"
					id="newsletter_email"
					className="advads-input-text"
					placeholder="Enter email address"
					onChange={() => {}}
				/>
				<button className="button button-hero button-primary">
					{wizard.newsletter.btnLabel}
				</button>
			</div>
		</>
	);
}
