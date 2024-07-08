/**
 * External Dependencies
 */
import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { wizard } from '@advancedAds/i18n';

/**
 * Internal Dependencies
 */
import { adminUrl } from '@utilities';
import Divider from '@components/Divider';
import Newsletter from '../Newsletter';
import Checkmark from '../../icons/Checkmark';
import Upgradebox from '../../icons/UpgradeBox';

function ListItem({ title, text }) {
	return (
		<div className="flex w-full gap-x-3 items-center">
			<div className="mt-2">
				<Checkmark />
			</div>
			<div className="grow">
				<strong>{title}</strong> {text}
			</div>
		</div>
	);
}

export default function Congrats({ options }) {
	const [result, setResult] = useState(null);

	if (null === result) {
		apiFetch({
			path: '/advanced-ads/v1/onboarding',
			method: 'POST',
			data: options,
		}).then((response) => {
			setResult(response);
		});
	}

	return (
		<>
			<div className="flex items-center gap-x-12">
				<p className="text-justify" dangerouslySetInnerHTML={{ __html: wizard.congrats.stepHeading }} />
				{result &&
					result.success &&
					'' !== result.placementEditLink && (
						<a
							href={result.placementEditLink}
							className="button button-hero"
						>
							{wizard.congrats.btnEditPlacement}
						</a>
					)}
			</div>
			<div className="flex justify-between items-center gap-x-12">
				<p dangerouslySetInnerHTML={{ __html: wizard.congrats.liveHeading}} />
				{result &&
					result.success &&
					'' !== result.postLink && (
						<div className="mr-3">
							<a
								href={result.postLink}
								className="button button-hero button-primary"
							>
								{wizard.congrats.btnLiveAd}
							</a>
						</div>
					)}
			</div>
			<Divider />
			<Newsletter />
			<Divider />
			<h1 className="!mt-0">{wizard.congrats.upgradeHeading}</h1>
			<div className="flex items-center gap-x-12">
				<p className="text-justify">{wizard.congrats.upgradeText}</p>
				<button className="button button-hero !bg-red-600 !border-red-700 !text-white !text-lg tracking-wide !py-4">
					{wizard.congrats.btnUpgrade}
				</button>
			</div>

			<div className="flex gap-x-12 items-center">
				<div className="space-y-2 mt-4 text-lg tracking-wide grow">
					{wizard.congrats.upgradePoints.map((point, index) => (
						<ListItem key={`point-${index}`} {...point} />
					))}
				</div>
				<div>
					<Upgradebox className="w-40" />
				</div>
			</div>
			<Divider />
			<div className="text-center">
				<a
					href={adminUrl('admin.php?page=advanced-ads')}
					className="button button-hero button-primary"
				>
					{wizard.congrats.btnDashboard} &rarr;
				</a>
			</div>
		</>
	);
}
